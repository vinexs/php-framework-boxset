# Vinexs Web Framework
It is a light weight web application framework. It is developed based on MVC framework.

## Requirement
- Web server with URL Rewrite Module
- php > 5.0

## Security Warning
Please make sure your *URL Rewrite Module* work properly or client can read manifest.json content with just typing the path.

## How to setup
### 1. Modify manifest.json
| Param | Description | Example |
| --- | --- | --- |
| url.domain | Hosting domain name. | www.example.com |
| url.root | Url between domain and index.php. | "/" or "/project-name" |
| session.token | Login cookie name. | APP_TOKEN |
| session.encrypt | Login cookie encryption key. | abcdefg123456789 |
| application.activity | To store all activity in object. Index will execute the frist one by default.| [See below](#example-of-activity-object) |
| database | To store all database connections. | [See below](#example-of-database-object) |

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
├── assets
│   ├── main
│   └── ...
├── main
|   └──(activity)
├── plugins
|   ├── BaseModel.class.php
|   └── Mcrypt.class.php
├── .htaccess
├── index.php
└── manifest.json
```

### Activity
```
main
├── controllers
|   ├── MainApp.class.php
│   └── ...
├── languages
|   ├── en.ini
|   ├── zs.ini
|   ├── zt.ini
│   └── ...
├── models
|   ├── LanguageModel.class.php
|   ├── SessionModel.class.php
│   └── ...
├── settings
|   ├── setting.php
│   └── ...
└── views
    ├── element_sample.php
    ├── frame_layout.php
    └── ...
```
* Folder "controllers" store all the controller. Those files should extend form **index.php** .Can be load by **load_controller($controller_name)**
* Folder "languages" store languages ini files. It can be empty if using database languages.
* Folder "models" store database model files. Those files should extend from **/plugin/BaseModel.class**
* File in Folder "settings" will load automatically. Can be retrive with **$this->setting['foo']**.
* Folder "views" will store view files. Those file can be call by **load_view($view, $vars)** .

___
## Functions
### index.php
#### File loading

```php
object load_controller(string $controller_name)
```
Load controller file and return as object.

| Param | Description |
| --- | --- |
| $controller_name | Controller name inside controllers folder with out .class.php extension. | 
| Return value | Return controller object or boolean false. |

```php
object load_model(string $model_name, string $db_name)
```
Load model file and return as object.

| Param | Description |
| --- | --- |
| $model_name | Load model name inside models directory with out .class.php extension. |
| $db_name | Which database name the model will connect. |
| Return value | Model object or boolean false. |
   
```php
array load_setting([$current_only = true])
```
Load all file in target activity's [settings] folder and store $SETTING variable to class variable for future use.

| Param | Description |
| --- | --- |
| $current_only | Return only current activity settings. |
| Return value | Setting variables |

```php
bool load_view(string $view [,array $vars = array()])
```
Load view file to output.

| Param | Description |
| --- | --- |
| $view | View file name inside views directory with out .php extension. |
| $vars | Variable to pass through view. |
| Return value | Boolean to repersent load view success or fail. |    

```php
bool load_plugin(string $plugin_name)
```
Load plugin class.php file to process.

| Param | Description |
| --- | --- |
| $plugin_name | Plugin name in plugin directory without .class.php extension. |
| Return value | Boolean to repersent load class success or fail. |

```php
void load_file(string $file_path)
```
Output file in specific location, do not echo any content before load file.

| Param | Description |
| --- | --- |
| $file_path | Output file absolute path. |

#### Page Handling
```php
void redirect(string $url_path)
```
Quick way to redirect visitor to another page.

| Param | Description |
| --- | --- |
| $url_path | Redirect to more specific path. |

```php
mixed get(string $name [, string $type = null [, $default = null]])
```
Return $_GET variable with pre-set exception return

| Param | Description |
| --- | --- |
| $name | $_GET variable name. |
| $type | Expected data type. |
| $default | Default value. |
| Return value | Specify $_GET variable result. |

```php
mixed post($name, $type = null, $default = null)
```
Return $_POST variable with pre-set exception return

| Param | Description |
| --- | --- |
| $name | $_POST variable name. |
| $type | Expected data type. |
| $default | Default value. |
| Return value | Specify $_POST variable result. |

#### Page Response
```php
void show_error($error [, $line_no = null])
```
Return error message and output to client

| Param | Description |
| --- | --- |
| $error | Http error status code |
| $line | Line no to report error. |

```php
void show_xml(bool $status [, mixed $data = ''])
```
Return data as xml content to client

| Param | Description |
| --- | --- |
| $status | Result in successful status or error status. |
| $data | String or Array data to output. |

```php
void show_json(bool $status, [, mixed $data = null])
```
Return data as json object to client

| Param | Description |
| --- | --- |
| $status | Result in successful status or error status. |
| $data | String or Array data to output. |

#### Multiple Language
```php
string lang(string $language_code)
```
Return localized text from loaded text object.

| Param | Description |
| --- | --- |
| $language_code | Language represent code. |
| Return value | string with correct language. |

```php
string get_lang_var($json)
```
Un-serialize language object and return suitable language content

| Param | Description |
| --- | --- |
| $json | Stringify json string. |
| Return value | A single string with correct language. |






