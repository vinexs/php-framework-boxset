# Vinexs Web Framework
It is a light weight web application framework. It is developed based on MVC framework.

## How to setup
### 1. Modify manifest.json
| Param | Description | Example |
| --- | --- | --- |
| url.domain | Hosting domain name. | www.example.com |
| url.root | Url between domain and index.php. | "/" or "/project-name" |
| session.token | Login cookie name. | APP_TOKEN |
| session.encrypt | Login cookie encryption key. | abcdefg123456789 |
| application.activity | To store all activity in object. Index will execute the frist one by default.| See below |
| database | To store all database connections. | See below |

#### Example of activity object
```json
"main": {
    "launch": "MainApp",
    "storage": "/server/absolute/path/paste/here/",
    "languageSource": "testdb",
    "language": [
        "zt",
        "zs",
        "en"
    ]
}
```
"main" is a user define name.

| Param | Description | Example |
| --- | --- | --- |
| main.launch | Launch Class name with out extension | MainApp |
| main.storage | OPTIONAL variable to set writable path | .. |
| main.languageSource | Set language source from database or ini file | "dbname" or "ini" | 
| main.language | Supported languages in array | en, zt, zs, js ... |

#### Example of database object
```json
"test_db": {
    "host": "localhost",
    "user": "sqluser",
    "password": "sql-password",
    "db_name": "sql-db-name"
}
```
"test_db" is a user define name, which will be used in "main.languageSource" or loadModel()

| Param | Description | Example |
| --- | --- | --- |
| test_db.host | MySQL server host | 127.0.0.1 |
| test_db.user | MySQL access user | user |
| test_db.password | MySQL user password | ******** |
| test_db.db_name | MySQL database name | testing_database |







