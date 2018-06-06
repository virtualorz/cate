# Installation #

### install by composer ###
<pre><code>
composer require virtualorz/cate
</code></pre>

### edit config/app.php ###
<pre><code>
'providers' => [
    ...
    Virtualorz\Fileupload\FileuploadServiceProvider::class,
    Virtualorz\Cate\CateServiceProvider::class,
    ...
]

'aliases' => [
    ...
    'Fileupload' => Virtualorz\Fileupload\FileuploadFacade::class,
    'Cate' => Virtualorz\Cate\CateFacade::class,
    ...
]
</code></pre>

### migration db table ###
<pre><code>
php artisan migrate
</code></pre>

# usage #
#### 1. get cate list data ####
<pre><code>
$dataArray = Cate::list('use type');
</code></pre>
use type : eg. news, member , product ...etc, different type in your application
$dataArray : return array in two elements : [without_sub_cate_structure,with_sub_cate_structure]

#### 2. add data to cate ####
<pre><code>
Cate::add('use type');
</code></pre>
with request variable name required : cate-parent_id,ate-name,cate-order,cate-enable,cate-select_photo

#### 3. get cate detail ####
<pre><code>
$dataRow = Cate::detail($cate_id);
</code></pre>

#### 4. edit data to cate ####
<pre><code>
Cate::edit();
</code></pre>
with request variable name required : cate-parent_id,ate-name,cate-order,cate-enable,cate-select_photo

#### 5. delete cate data ####
<pre><code>
Cate::delete();
</code></pre>
with request variable name required : id as integer or id as array

#### 6. enable cate data ####
<pre><code>
Cate::enable($type);
</code></pre>
with request variable name required : id as integer or id as array
$type is 0 or1 , 0 to disable i to enable




