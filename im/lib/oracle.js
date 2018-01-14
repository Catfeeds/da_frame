var oracle = require('oracle');
var db_config = require('../config');
var c = console.log;
var db = '';
var tablepre = db_config.config['tablepre'];

var connectData = {
    hostname: db_config.config['host'],
    port: db_config.config['port'],
    database: db_config.config['database'], // System ID (SID)
    user: db_config.config['user'],
    password: db_config.config['password']
}

function db_connect() {
    oracle.connect(connectData, function(err, connection) {
        if (err) {
            console.log("Error connecting to oracle:", err);
            throw err;
        } else {
            db = connection;
            console.log('   oracle connected');
        }
    });
}

db_connect();

function get_query(sql) {
	var db_query = '';

	if ( typeof sql === "object" ) {
		var n = 0;
		for (var k in sql){
			if ( typeof sql[k] === "string" ) {
				n++;
				db_query += k+"='"+sql[k]+"' AND ";
			}
		}
		db_query += ' 2 > 1';
	} else {
		db_query = sql;
	}

	return db_query;
}

function get_list(rows) {
    var list = new Array();
	for (var n in rows){
	    var row = new Object();
	    var msg = rows[n];
    	for (var k in msg){
    	    var v = msg[k];
    	    k = k.toLowerCase();
    		row[k] = v;
    	}
    	list[n] = row;
	}

	return list;
}

exports.get_msg_list = function (sql, cb) {
	var db_query = 'SELECT * FROM ';
	var table = tablepre+'chat_msg ';
	db_query += table+' WHERE ';

	db_query += get_query(sql);
	db.execute(db_query, [], function(err, rows) {
	    rows = get_list(rows);
	    cb(rows);
	});
}

exports.del_msg = function (sql) {
	var db_query = 'DELETE FROM ';
	var table = tablepre+'chat_msg ';
	db_query += table+' WHERE ';

	db_query += get_query(sql);
	db.execute(db_query, [], function(err, rows) {
	    if (err) {
	        db_connect();
	    }
	});
}

exports.update_msg = function (sql, values) {
	var db_query = 'UPDATE ';
	var table = tablepre+'chat_msg SET ';
	db_query += table;
	for (var k in values){
		db_query += k+"='"+values[k]+"' ";
	}
	db_query += ' WHERE ';

	db_query += get_query(sql);
	db.execute(db_query, [], function(err, rows) {
	    if (err) {
	        db_connect();
	    }
	});
}

exports.get_store_msg = function (sql, cb) {
	var db_query = 'SELECT * FROM ';
	var table = tablepre+'store_msg ';
	db_query += table+' WHERE ';

	db_query += get_query(sql);
	db.execute(db_query, [], function(err, rows) {
	    rows = get_list(rows);
	    cb(rows);
	});
}
