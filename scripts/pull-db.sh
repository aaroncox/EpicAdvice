# These commands will duplicate R2Db onto your local.
# db.swtor.remove();
# db.copyDatabase("r2db","r2db","localhost:27018","r2db","(-r2-Db-)*-*-*DB");

ssh dev -t -t " ssh -L27018:localhost:27017 greymass.com 'sleep 5; exit'" > /dev/null 2>/dev/null &

cat copydb.js | ssh dev "sleep 2; mongo"