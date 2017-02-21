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
"main" is a user define name. It's pointing to framework root directory folder "main"

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

### 2. Test your website
If using above example, open your browser and access http://www.example.com/ or http://www.example.com/project-name/ .

___
## File structure
### Framework
```
| -- assets
|     \ main
|     | ...
| -- main
|     \ (activity)
| -- plugins
|     \ BaseModel.class.php
|     | Mcrypt.class.php
| .htaccess
| index.php
| manifest.json
```

### Activity
```
main
|-- controllers
|     \ MainApp.class.php
|-- languages
|     \ en.ini
|     | zs.ini
|     | zt.ini
|-- models
|     \ LanguageModel.class.php
|     | SessionModel.class.php
|-- settings
|     \ setting.php
|-- views
      \ element_sample.php
      | frame_layout.php
```
* Folder "controllers" store all the controller. Those files should extend form **index.php** .Can be load by **load_controller($controller_name)**
* Folder "languages" store languages ini files. It can be empty if using database languages.
* Folder "models" store database model files. Those files should extend from **/plugin/BaseModel.class**
* File in Folder "settings" will load automatically. Can be retrive with **$this->setting['foo']**.
* Folder "views" will store view files. Those file can be call by **load_view($view, $vars)** .

___
## Functions
### index.php
load_controller($controller_name)
load_model($model_name, $db_name)
load_setting($current_only = true)
load_view($view, $vars = array())
load_plugin($plugin_name)
load_file($file_path)

redirect($url_path)
get($name, $type = null, $default = null)
post($name, $type = null, $default = null)

show_error($error, $line_no = null)
show_xml($status, $data = '')
show_json($status, $data = null)

lang($language_code)
get_lang_var($json)
