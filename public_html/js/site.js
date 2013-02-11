(function( $, undefined ) {
	$.fn.newContent = function() {
		this.find('a.vote-link').each(setupVoteLink);
		this.find('div.tags-ui').each(setupTagger);
		this.find('div.post-stub').each(setupStubLoad);
	};

	function setupStubLoad() {
		var post = $( this );
		post.find(".stub-title > a").one('click', function(e) {
			$.ajax({
				url: this.href,
				dataType: "json"
			}).success(function(data) {
				if(data.error) {
					post.append('<div class="ui-state-error"><h2>Error Loading Page</h2><p>Sorry, there was an error loading this document</p></div>');
				} else {
					post.find(".vote-controls").show();
					var $stub = post.find(".stub-loadin");
					$stub.html($('<div class="ui-helper-clearfix">'+data.body+'</div>')).addClass('post-text transparent-bg rounded messageBody');
					$stub.append($('<div class="post-controls">').append(data.controls));
					$stub.r2bind();
				}
			});
			e.preventDefault();
		});
	}

	function setupVoteLink() {
		var link = $( this ),
			url = link.data('voteurl');

		link.click( function(e) {
			if ($(e.target).is("input")) return;
			e.preventDefault();
			
			if (link.is('.vote-flag')) {
				var widget = link.closest('.vote-widget');
				widget.width( widget.width() );
				widget.find('.vote-flag-popout').toggle().css({
					position: 'absolute',
					zIndex: 99999
				}).offset(
					link.offset() + 20
				).unbind("clickoutside").bind("clickoutside", function() {
					$(this).hide();
				});
				return;
			}
			sendVote();
		}).find('input').keydown(function(e) {
			if (e.which == $.ui.keyCode.ENTER) {
				sendVote();
			}
		});
		
		function sendVote() {
			link.addClass('ui-state-disabled');
			var data = {};
			link.find('input').each(function() {
				data[this.name] = this.value;
			});
			$.ajax({ url: url,
				dataType: "json",
				data: data,
				type: "POST"
			}).success( function( data ) {
				if ( data.error ) {
					var errMsg = $("<div class='ui-widget-content ui-state-error padded shadowy' title='error' style='float:left'>");
					errMsg.append($("<p/>").text(data.error));
					errMsg.appendTo("body").position({
						my: "left center",
						at: "right center",
						of: link,
						collision: "flip flip"
					}).delay(2000).fadeOut(1000, $.proxy( errMsg, "remove" ));
				} else {
					var $post = $( "#" + data.postType + "-" + data.post );
					$post.find('.vote-count').first().text(data.newScore.score || 0);
					if (/up|down/.test(data.yourVote)) {
						var other = data.yourVote == 'up' ? 'down': 'up';
						$post.find( '.vote-'+other )
							.removeClass( 'ui-state-active');
						$post.find( '.vote-'+data.yourVote )
							.toggleClass( 'ui-state-active', data.hasCast );
					} else {
						link.toggleClass( 'ui-state-active', data.hasCast );
					}
					link.closest('.vote-flag-popout').hide();
				}
				link.removeClass('ui-state-disabled');
			});
			
		}
	}

	$.ui && $.ui.autocomplete && (function() {
		var proto = $.ui.autocomplete.prototype,
			initSource = proto._initSource;

		function filter( array, term ) {
			var matcher = new RegExp( $.ui.autocomplete.escapeRegex(term), "i" );
			return $.grep( array, function(value) {
				return matcher.test( $( "<div>" ).html( value.label || value.value || value ).text() );
			});
		}

		$.extend( proto, {
			_initSource: function() {
				if ( this.options.html && $.isArray(this.options.source) ) {
					this.source = function( request, response ) {
						response( filter( this.options.source, request.term ) );
					};
				} else {
					initSource.call( this );
				}
			},

			_renderItem: function( ul, item) {
				return $( "<li></li>" )
					.data( "item.autocomplete", item )
					.append( $( "<a></a>" )[ this.options.html ? "html" : "text" ]( item.label ) )
					.appendTo( ul );
			}
		});
	})();

	function setupTagger() {
		var div = $( this ),
			hidden = div.find( 'input[type="hidden"]' ),
			url = hidden.attr( 'data-search-url' ),
			tags = $.parseJSON( hidden.val() ) || [],
			cards = div.find( '.db-card' ),
			cardHolder = $( "<div>" ).appendTo( div ).append( cards ),
			adder = $( "<input placeholder='Search Tags...'>" ).appendTo( div ),
			remove = $(" <span class='ui-icon ui-icon-close ui-corner-all' /> ").css({
				position: 'absolute',
				top: 0,
				right: 0,
				backgroundColor: '#777'
			});

		function updateTags() {
			hidden.val(JSON.stringify(tags));
		}

		adder.autocomplete({
			html: true,
			source: function(request, response) {
				$.ajax({
					url: url,
					data: { q: request.term, format: "json" },
					dataType: "json"
				}).success(function(data) {
					response($.map(data.results, function(v) {
						var ref = {};
						if (v.$new) {
							ref.$new = v.$new;
							ref.name = v.name;
						} else {
							ref.$ref = v.$ref;
							ref.$id = v.$id;
						}
						return {label: v.label, card: v.card, ref: ref, value: v.name};
					}));
				}).error(function() {
					response([]);
				});
			},
			select: function(e, ui) {
				cards = cards.add( $( ui.item.card ).appendTo( cardHolder ) );
				tags.push( ui.item.ref );
				updateTags();
				setTimeout(function() {
					adder.val('');
				},0);
				e.preventDefault();
			}
		});

		cardHolder.delegate('.db-card', 'mouseenter', function(e) {
			var card = $(this);
			$(this).css({position: 'relative'}).append(remove);
			remove.click(function() {
				tags.splice(card.index(), 1);
				updateTags();
				card.remove();
			});
		}).delegate('.db-card', 'mouseleave', function(e) {
			remove.remove();
		});
		adder.autocomplete('widget').addClass('epicdb-autocomplete');
	}

	$(function() {
		$(document.documentElement).newContent();
	});
})( jQuery );

/*
 * jQuery UI Autocomplete HTML Extension
 *
 * Copyright 2010, Scott González (http://scottgonzalez.com)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * http://github.com/scottgonzalez/jquery-ui-extensions
 */
$.ui && $.ui.autocomplete && (function( $ ) {


})( jQuery );