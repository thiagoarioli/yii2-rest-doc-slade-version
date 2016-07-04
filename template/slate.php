<?
$basePath = "http://localhost/SEBAPI/api/api/web/v1";


?>
---
title: API Reference

language_tabs:
- shell

includes:
- errors

search: true
---

# Introduction

Welcome to the API! You can use our API to access all endpoints, which can get information in our database.

We have language bindings in Shell! You can view code examples in the dark area to the right.


# Authentication

> To authorize, use this code:

```shell
# With shell, you can just pass the correct header with each request
curl "api_endpoint_here"
-H "Authorization: Bearer meowmeowmeow"
```

> Make sure to replace `meowmeowmeow` with your API key.

Uses API keys to allow access to the API. You can ask for a developer to generate your API key.

API expects for the API key to be included in all API requests to the server in a header that looks like the following:

`Authorization: Bearer meowmeowmeow`

<aside class="notice">
    You must replace `meowmeowmeow` with your personal API key.
</aside>

<?php foreach ($controllers as $controller): ?>

# <?=explode(' ',$controller->shortDescription)[1]?>

<?php foreach($controller->actions as $action => $value):
switch ($action){
    case "view":
    case "update":
    case "delete":
        $isObject = true;
        break;
    default:
        $isObject = false;
}
?>
## <?=$action?>


```shell

<? if($isObject){ ?>
curl "<?=$basePath?>/<?=$controller->path?>/<id>"
<? }else if (!isset($controller->actions[$action]['short'])){ ?>
curl "<?=$basePath?>/<?=$controller->path?>"
<? }else{ ?>
curl "<?=$basePath?>/<?=$controller->path?>/<?=$action?>"
<?}?>
-H "Authorization: Bearer meowmeowmeow"
```

> The above command returns JSON structured like this:

```json
<? if($isObject) { ?>
{
"success": true,
"data":
{
<?
$count = 1;
foreach ($controller->model->properties as $item):?>
"<?= explode(' ', $item['param'])[0] ?>": <?= ($item['type'] == 'integer') ? rand(1, 10) : '"meow"' ?>
<?if (count($controller->model->properties) > $count){?>
,
<?}
$count++;?>
<? endforeach; ?>

}
<?}else{?>
{
"success": true,
"data":[
{
<?
$count = 1;
foreach ($controller->model->properties as $item):?>
"<?= explode(' ', $item['param'])[0] ?>": <?= ($item['type'] == 'integer') ? rand(1, 10) : '"meow"' ?>
<?if (count($controller->model->properties) > $count){?>
,
<?}
$count++;?>
<? endforeach; ?>

},
{
<?
$count = 1;
foreach ($controller->model->properties as $item):?>
"<?= explode(' ', $item['param'])[0] ?>": <?= ($item['type'] == 'integer') ? rand(1, 10) : '"meow"' ?>
<?if (count($controller->model->properties) > $count){?>
,
<?}
$count++;?>
<? endforeach; ?>

}
]
}

<?}?>
```

<?= isset($controller->actions[$action]['short']) ? $controller->actions[$action]['short'] : 'Rest Default Call'?>

### HTTP Request
<? if($isObject){ ?>
`<?=$value['request'][0]?> <?=$basePath?>/<?=$controller->path?>/<id>`
<? }else if (!isset($controller->actions[$action]['short'])){ ?>
`<?=$value['request'][0]?> <?=$basePath?>/<?=$controller->path?>`
<? }else{ ?>
`<?=$value['request'][0]?> <?=$basePath?>/<?=$controller->path?>/<?=$action?>`
<?}?>
### Parameters
<table>
<thead>
<td>Type</td>
<td>Parameter</td>
<td>Description</td>
</thead>
<tbody>
<?php
if(isset($controller->actions[$action]['tags'])) {?>

<?
foreach ($controller->actions[$action]['tags'] as $item):
if($item['type'] == 'integer' || $item['type'] == 'string' ||  $item['type'] == 'object'):?>
<tr><td><?= $item['type'] ?></td> <td> <?= explode(' ',$item['param'])[0] ?></td> <td> <?= @explode(' ',$item['param'])[1] ?></td></tr>
<?php
endif;
endforeach;
    ?>

<?php
}else{
    ?>
<?php foreach ($controller->model->properties as $item):
if($item['type'] == 'integer' || $item['type'] == 'string' || $item['type'] == 'object'):?>
<tr><td><?= $item['type'] ?></td> <td> <?= explode(' ',$item['param'])[0] ?></td> <td> <?= @explode(' ',$item['param'])[1] ?></td></tr>
<?php
endif;
endforeach;
}
?>
</tbody>
</table>


<aside class="success">
    Remember — always use the authenticate!
</aside>

<?php endforeach?>

<?php endforeach ?>


