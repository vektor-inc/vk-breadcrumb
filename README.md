# VK Bread Crumb

```
composer require vektor-inc/vk-breadcrumb
```

load autoload
```
require_once dirname( __FILE__ ) . '/vendor/autoload.php';
```

Display bread crumb
```
use VektorInc\VK_Breadcrumb\VkBreadcrumb;
$vk_breadcrumb      = new VkBreadcrumb();
$breadcrumb_options = array(
	'id_outer'        => 'breadcrumb',
	'class_outer'     => 'breadcrumb',
	'class_inner'     => 'container',
	'class_list'      => 'breadcrumb-list',
	'class_list_item' => 'breadcrumb-list__item',
);
$vk_breadcrumb->the_breadcrumb( $breadcrumb_options );
```
