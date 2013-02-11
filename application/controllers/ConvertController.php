<?php
/**
 * ConvertController
 *
 * undocumented class
 *
 * @author Aaron Cox <aaronc@fmanet.org>
 * @param undocumented class
 * @package undocumented class
 **/
class ConvertController extends EpicDb_Controller_Action
{
		protected $_parseLimit = 100000;
	
		public function init() {
			set_time_limit(0);
			date_default_timezone_set('GMT');
			parent::init();
		}
		protected function parseNode($node, $keyMapping = array(), $ignoreKeys = array()) {
			foreach ($node->childNodes as $keyNode) {
						// skip non 'element' nodes
						if ($keyNode->nodeType != XML_ELEMENT_NODE) continue;
						// lowercase first by default
						$keyName = lcfirst($keyNode->nodeName);
						if (in_array($keyName, $ignoreKeys)) continue;
						if (isset($keyMapping[$keyName])) {
								$keyName = $keyMapping[$keyName];
						}

						$data[$keyName] = $this->mungeValue($keyNode->nodeValue);
				}
				return $data;
		}

		protected function mungeValue($value) {
				if (preg_match("/^-?\d+$/", $value)) {
					// only digits, use as an int
					return (int)$value;
				}
				if (preg_match("/^-?\d*\.\d*$/", $value)) {
					return (float)$value;
				}
				if (preg_match("/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d{1,3})?$/", $value)) {
					return strtotime($value);
				}
				if ($value == 'true') return true;
				if ($value == 'false') return false;
				return $value;
		}

		protected function getXPath($type) {
				$file = file_get_contents("../stackdata/$type.xml");
				$dom = new DOMDocument();
				$dom->loadXml($file);
				// get the xpath query
				$xpath = new DOMXPath($dom);
				return $xpath;
		}

		protected function copyToMongo($array, $mongo) {
				foreach ($array as $k=>$v) $mongo->$k = $v;
		}

		public function baseRecordAction() {
			$record = R2Db_Mongo_Record::fetchOne(array("id" => 1));
			if(!$record) {
				$record = new R2Db_Mongo_Record();
			}
			$record->name = "EpicAdvice Conversion Placeholder";
			$record->_type = "skill";
			$record->save();
			var_dump($record);
			exit;
		}

		public function commentsFromStackAction() {
			$highestId = 0;
			// Things to Ignore
			$ignoreKeys = array('iPAddress');

			// Things to Map
			$keyMapping = array(
				'text' => 'body',
				'creationDate' => '_created',
			);

			// Target XMl
			$xpath = $this->getXPath("PostComments");
			$posts = $xpath->evaluate('//PostComments/row');
			foreach ($posts as $post) {
				$parsed = $this->parseNode($post, $keyMapping, $ignoreKeys);
				$highestId = max($highestId, (int)$parsed['id']);
				
				if(!isset($parsed['userId'])) continue;
				if(!isset($parsed['postId'])) continue;

				if($parsed['id'] > $this->_parseLimit) continue;
				$class = EpicDb_Mongo::dbClass('question-comment');
				$post = EpicDb_Mongo::db('question-comment')->fetchOne(array("id" => (int) $parsed['id']));
				if($post) continue;
				
				$post = new $class();
				// Copy shit to mongo
				$this->copyToMongo($parsed, $post);

				// Hook the parent post as _parent
				$parent = EpicDb_Mongo::db('post')->fetchOne(array('id' => $parsed['postId']));
				if(!$parent) continue;
				$post->_parent = $parent;
				unset($post->postId);

				// Hook the author of the comment
				$profile = EpicDb_Mongo::db('user')->fetchOne(array('id' => $parsed['userId']));
				$post->tags->tag($profile, 'author');
				unset($post->userId);

				// Save it
				$post->save();
			}
			$lastId = MW_Mongo_Sequence::setNextSequence('question-comment', $highestId);
			echo "Imported up to ID #".$lastId;
			exit;
		}

		public function votesFromStackAction()
		{
			$xpath = $this->getXPath("Posts2Votes");
				// <Id>3</Id>
				// <PostId>2</PostId>
				// <UserId>1</UserId>
				// <VoteTypeId>1</VoteTypeId>
				// <CreationDate>2009-10-06T20:18:30.497</CreationDate>
				// <TargetUserId>3</TargetUserId>
				// <TargetRepChange>15</TargetRepChange>
				// <VoterRepChange>2</VoterRepChange>
				// <IPAddress>10.2.1.19</IPAddress>

			$votes = $xpath->evaluate("//Posts2Votes/row");

			$postDb = EpicDb_Mongo::db('post');
			$userDb = EpicDb_Mongo::db('user');
			
			$voteTypes = array(
				'1' => 'accept',
				'2' => 'up',
				'3' => 'down',
				'4' => 'offensive',
				'5' => 'favorite',
				'6' => 'close',
				'12'=> 'spam',
				'13'=> 'moderator',
			);
			foreach ($votes as $vote) {
				$parsed = $this->parseNode($vote);
				if($parsed['id'] > $this->_parseLimit) continue;
				$pquery = array(
					'_type' => array( '$in' => array('question', 'answer') ),
					'id' => $parsed['postId'],
				);
				$user = $userDb->fetchOne(array('id'=>$parsed['userId']));
				$post = $postDb->fetchOne($pquery);
				
				if (isset($voteTypes[$parsed['voteTypeId']])) {
					$type = $voteTypes[$parsed['voteTypeId']];
				} else {
					continue; 
				}
				
				if (!$user || !$post) {
					continue;
				}
				
				$vote = EpicDb_Vote::factory($post, $type ,$user);
				$vote->setImportMode($parsed['creationDate']);
				$vote->cast();
			}
			
			foreach ($postDb->fetchAll(array(
				'_type'=>array('$in'=>array('question','answer')),
			)) as $post) {
				$post->votes = EpicDb_Vote::countVotes($post);
				$post->save();
			}
			exit;
		}

		public function postsFromStackAction() {
			$highestId = 0;
				// Things to Ignore
				$ignoreKeys = array('answerCount', 'commentCount');

				// Things to Map
				$keyMapping = array(
					'websiteUrl' => 'url',
					'creationDate' => '_created',
					'tags' => 'text-tags',
				);

				// Target XMl
				$xpath = $this->getXPath("Posts");
				$posts = $xpath->evaluate('//Posts/row');

				$filter = new MW_Filter_Slug();

				foreach ($posts as $post) {
						$parsed = $this->parseNode($post, $keyMapping, $ignoreKeys);
						$highestId = max($highestId, (int)$parsed['id']);

						if($parsed['id'] > $this->_parseLimit) continue;
						if($parsed['postTypeId'] == 1) {
							// This is a question
							$class = EpicDb_Mongo::dbClass('question');
							$type = 'question';
						} elseif($parsed['postTypeId'] == 2) {
							// This is an answer
							$class = EpicDb_Mongo::dbClass('answer');
							$type = 'answer';
						} else {
							throw new Exception("This post has an invalid postTypeId");
						}
						$post = EpicDb_Mongo::db($type)->fetchOne(array("id" => (int) $parsed['id']));
						if($post) continue;

						$post = new $class();

						
						$post->tags;
							
						// Convert the tags into the text-tags array (Improve later)
						if(isset($parsed['text-tags'])) {
							$parsed['text-tags'] = str_replace(array(
								"\xC3\xB6",
								"û",
							), array(
								"-",
								".",
							), $parsed['text-tags']); 
							preg_match_all("/é([^à]+)/", $parsed['text-tags'], $matches);
							$parsed['text-tags'] = $matches[1];
						}
						// Get the profile
						if(!isset($parsed['ownerUserId'])) continue;
						$profile = EpicDb_Mongo::db('user')->fetchOne(array('id' => $parsed['ownerUserId']));
						$this->copyToMongo($parsed, $post);
						// Unset the ownerUserId and set it as a tag
						if($profile) {
							unset($post->ownerUserId);
							// $post->_profile = $profile;
							if(!$post->tags->getTag('author')) {
								$post->tags->tag($profile, 'author');
							}
						} else {
							continue;
						}
						// Check for Deleted Data, if so, add the deleted flag and unset
						if($post->deletionDate) {
							unset($post->deletionDate);
							$post->_deleted = true;
						}
						// Check for Parent, if so, add it and unset
						if($post->parentId) {
							$parent = EpicDb_Mongo::db('question')->fetchOne(array("id" => (int) $post->parentId));
							if($parent) {
								$post->_parent = $parent;
								unset($post->parentId);
							} else {
								continue;
							}
							// var_dump($post->export()); exit;
						}
						// Convert score into array
						if($post->score) {
							$post->votes = array(
								'score' => $post->score
							);
							unset($post->score);
						}
						// Set a placeholder record (if its not an answer to another post)
						// if(!$post->tags->getTag('subject')) {
						// 	$post->tags->tag(EpicDb_Mongo_Record::fetchOne(), 'subject');
						// }
						// Set the default tags
						if (!empty($parsed['text-tags'])) foreach($parsed['text-tags'] as $tagText) {
							$query = array(
								'name' => $tagText,
							);
							$tag = EpicDb_Mongo::db('tag')->fetchOne($query);
							if(!$tag) {
								$tag = EpicDb_Mongo::newDoc('tag');
								$tag->name = $tagText;
								$tag->slug = $filter->filter($tagText);
								$tag->save();
							}
							$post->tags->tag($tag);
						}
						// Set the type
						$post->_type = $type;
						if(!is_int($post->_created)) {
							var_dump($post->export());
							exit;
						}
						$post->save();
				}
				$lastId = MW_Mongo_Sequence::setNextSequence('question', $highestId);
				$lastId = MW_Mongo_Sequence::setNextSequence('answer', $highestId);
				echo "Imported up to ID #".$lastId;
				exit;
		}

		public function usersFromStackAction() {
			// Connect to our profile db
			$config = array(
			    'host'     => 'localhost',
			    'username' => 'root',
			    'password' => '',
			    'dbname'   => 'epicadvice',
			);
			$db = Zend_Db::factory('PDO_MYSQL', $config);
			$table = new Zend_Db_Table(array("db" => $db, "name" => "epic_db_profile"));
			
			// Things to Ignore
			$ignoreKeys = array('badgeSummary','hasReplies','hasMessage','location','birthday','preferencesRaw');

			// Things to Map
			$keyMapping = array(
				'websiteUrl' => 'url',
				'displayName' => 'name',
				'displayNameCleaned' => 'slug',
				'aboutMe' => 'bio',
			);

			// Target XMl
			$xpath = $this->getXPath("Users");
			$users = $xpath->evaluate('//Users/row');

			$highestId = 0;
			foreach ($users as $user) {
					$parsed = $this->parseNode($user, $keyMapping, $ignoreKeys);
					if($parsed['id'] == -1) continue;
					if($parsed['id'] > $this->_parseLimit) continue;
										
					$profile = EpicDb_Mongo::db('user')->fetchOne(array('id' => (int)$parsed['id']));
					if (!$profile) {
						$userClass = EpicDb_Mongo::dbClass('user');
						$profile = new $userClass;
						$profile->_type = 'user';
						$user = new MW_Auth_Mongo_Role();

						foreach(array("openId", "openIdAlt") as $key) {
							if ( !empty($parsed[$key]) ) {
								$auth = $user->auth->new();
								$auth->openid = $parsed[$key];
								$user->auth->addDocument($auth);
							}
						}

						$user->save();
						$profile->user = $user;
						$profile->save();
					}
					
					// Data from the Profiles DB
					$select = $table->select()->where("user_id = ?", $parsed['id']);
					$row = $table->fetchAll($select)->current(); 
					// var_dump($row); exit;
					// var_dump($profile); exit;
					if($row) {
						$query = array(
							'name' => $row->character,
							'realm' => $row->realm,
							'region' => $row->region,
							'faction' => (int) $row->faction,
						);
						$char = EpicDb_Mongo::db('character')->fetchOne($query);
						if(!$char) {
							$char = EpicDb_Mongo::newDoc('character');
							$this->copyToMongo($query, $char);
							$char->tags->tag($profile, 'owner');
							$char->save();							
						}
						$profile->characters->addDocument($char);
					}
					$this->copyToMongo($parsed, $profile);
					// Save it onto the profile
					$profile->save();

					$highestId = max($highestId, (int)$parsed['id']);
			}
			$lastId = MW_Mongo_Sequence::setNextSequence('user', $highestId);
			echo "Imported up to ID #".$lastId;
			exit;
		}
} // END class ConvertController