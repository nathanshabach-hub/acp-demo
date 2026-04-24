/**
 * ACP Portal – IndexedDB Data Layer
 * Stores offline data and sync queue entries.
 */
var AcpDB = (function() {
    var DB_NAME = 'acp_portal';
    var DB_VERSION = 1;
    var db = null;

    // Store names
    var STORES = {
        SYNC_QUEUE:  'sync_queue',   // pending POST/PUT operations
        CACHE_DATA:  'cache_data',   // cached GET responses (reference data)
        STUDENTS:    'students',     // local student records
        EVENTS:      'events',       // cached event catalogue
        REGISTRATIONS: 'registrations' // cached convention registrations
    };

    function open() {
        return new Promise(function(resolve, reject) {
            if (db) { resolve(db); return; }
            var request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = function(e) {
                var d = e.target.result;

                // Sync queue – stores every offline POST/PUT to replay later
                if (!d.objectStoreNames.contains(STORES.SYNC_QUEUE)) {
                    var sq = d.createObjectStore(STORES.SYNC_QUEUE, { keyPath: 'id', autoIncrement: true });
                    sq.createIndex('status', 'status', { unique: false });
                    sq.createIndex('created', 'created', { unique: false });
                }

                // Generic cache for GET responses (keyed by URL)
                if (!d.objectStoreNames.contains(STORES.CACHE_DATA)) {
                    d.createObjectStore(STORES.CACHE_DATA, { keyPath: 'url' });
                }

                // Students mirror
                if (!d.objectStoreNames.contains(STORES.STUDENTS)) {
                    var st = d.createObjectStore(STORES.STUDENTS, { keyPath: 'id' });
                    st.createIndex('school_id', 'school_id', { unique: false });
                }

                // Events catalogue
                if (!d.objectStoreNames.contains(STORES.EVENTS)) {
                    d.createObjectStore(STORES.EVENTS, { keyPath: 'id' });
                }

                // Convention registrations
                if (!d.objectStoreNames.contains(STORES.REGISTRATIONS)) {
                    d.createObjectStore(STORES.REGISTRATIONS, { keyPath: 'id' });
                }
            };

            request.onsuccess = function(e) {
                db = e.target.result;
                resolve(db);
            };

            request.onerror = function(e) {
                reject(e.target.error);
            };
        });
    }

    // ---- Generic helpers ----

    function put(storeName, record) {
        return open().then(function(d) {
            return new Promise(function(resolve, reject) {
                var tx = d.transaction(storeName, 'readwrite');
                tx.objectStore(storeName).put(record);
                tx.oncomplete = function() { resolve(record); };
                tx.onerror = function(e) { reject(e.target.error); };
            });
        });
    }

    function get(storeName, key) {
        return open().then(function(d) {
            return new Promise(function(resolve, reject) {
                var tx = d.transaction(storeName, 'readonly');
                var req = tx.objectStore(storeName).get(key);
                req.onsuccess = function() { resolve(req.result); };
                req.onerror = function(e) { reject(e.target.error); };
            });
        });
    }

    function getAll(storeName) {
        return open().then(function(d) {
            return new Promise(function(resolve, reject) {
                var tx = d.transaction(storeName, 'readonly');
                var req = tx.objectStore(storeName).getAll();
                req.onsuccess = function() { resolve(req.result); };
                req.onerror = function(e) { reject(e.target.error); };
            });
        });
    }

    function remove(storeName, key) {
        return open().then(function(d) {
            return new Promise(function(resolve, reject) {
                var tx = d.transaction(storeName, 'readwrite');
                tx.objectStore(storeName).delete(key);
                tx.oncomplete = function() { resolve(); };
                tx.onerror = function(e) { reject(e.target.error); };
            });
        });
    }

    function clear(storeName) {
        return open().then(function(d) {
            return new Promise(function(resolve, reject) {
                var tx = d.transaction(storeName, 'readwrite');
                tx.objectStore(storeName).clear();
                tx.oncomplete = function() { resolve(); };
                tx.onerror = function(e) { reject(e.target.error); };
            });
        });
    }

    function getAllByIndex(storeName, indexName, value) {
        return open().then(function(d) {
            return new Promise(function(resolve, reject) {
                var tx = d.transaction(storeName, 'readonly');
                var idx = tx.objectStore(storeName).index(indexName);
                var req = idx.getAll(value);
                req.onsuccess = function() { resolve(req.result); };
                req.onerror = function(e) { reject(e.target.error); };
            });
        });
    }

    function count(storeName) {
        return open().then(function(d) {
            return new Promise(function(resolve, reject) {
                var tx = d.transaction(storeName, 'readonly');
                var req = tx.objectStore(storeName).count();
                req.onsuccess = function() { resolve(req.result); };
                req.onerror = function(e) { reject(e.target.error); };
            });
        });
    }

    // ---- Public API ----
    return {
        STORES: STORES,
        open: open,
        put: put,
        get: get,
        getAll: getAll,
        remove: remove,
        clear: clear,
        getAllByIndex: getAllByIndex,
        count: count
    };
})();
