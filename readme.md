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
</code></pre>

### migration db table ###
<pre><code>
php artisan migrate
</code></pre>

# usage #
#### 1. get cate list data ####
<pre><code>
$cate = new Cate();
$dataArray = $cate->list('use type');
</code></pre>
use type : eg. news, member , product ...etc, different type in your application
$dataArray : return array in two elements : [without_sub_cate_structure,with_sub_cate_structure]

#### 2. add data to cate ####
<pre><code>
$cate = new Cate();
$cate->add();
</code></pre>
with request variable name required : cate-parent_id,ate-name,cate-order,cate-enable,cate-select_photo

#### 3. get cate detail ####
<pre><code>
$cate = new Cate();
$dataRow = $cate->detail($cate_id);
</code></pre>

#### 4. edit data to cate ####
<pre><code>
$cate = new Cate();
$cate->edit();
</code></pre>
with request variable name required : cate-parent_id,ate-name,cate-order,cate-enable,cate-select_photo

#### 5. delete cate data ####
<pre><code>
$cate = new Cate();
$cate->delete();
</code></pre>
with request variable name required : id as integer or id as array

#### 6. enable cate data ####
<pre><code>
$cate = new Cate();
$cate->enable($type);
</code></pre>
with request variable name required : id as integer or id as array
$type is 0 or1 , 0 to disable i to enable




