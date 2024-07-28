<?PHP
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
 
$dir=__DIR__.'/';
include('config.php');
include('FileData.class.php');
include('Tpl.class.php');

$tpl=new Tpl();

/*
$menu=[
			['name'=>'Главная', 'url'=>'/', 'level'=>1], 
			['name'=>'Каталог', 'url'=>'/catalog/', 'level'=>1, 'sub'=>[
																			['name'=>'Дженерики виагры', 'url'=>'/catalog/viagra/', 'level'=>2],  
																			['name'=>'Дженерики сиалиса', 'url'=>'/catalog/sialis/', 'level'=>2, 'sub'=>[
																																					       ['name'=>'Vidalista 60', 'url'=>'/catalog/sialis/vidalista60', 'level'=>3] 
																																						]],
																			['name'=>'Дженерики левитры', 'url'=>'/catalog/levitra/', 'level'=>2, 'sub'=>[
																																							['name'=>'Vilitra 20', 'url'=>'/catalog/levitra/vilitra20', 'level'=>3],
																																							['name'=>'Vilitra 40', 'url'=>'/catalog/levitra/vilitra40', 'level'=>3],
																																							['name'=>'Vilitra 60', 'url'=>'/catalog/levitra/vilitra60', 'level'=>3]
																																						 ]],
																			['name'=>'Дженерики прилиджи', 'url'=>'/catalog/prilidgy/', 'level'=>2]
																	   ]], 
			['name'=>'Новости', 'url'=>'/news/', 'level'=>1]
	  ];


$tpl->setVars('name', 'url', 'level', 'sub');
$tpl->setTpl('<li class="sublvl{level}"><a href="{url}">{name}</a></li>{sub}');
$tpl->setTplLevels('sub', '<li class="sublvl{level}"><a href="{url}">{name}</a></li>', PHP_EOL.'<ul data-level="{level}">','</ul>', PHP_EOL);

$menu=$tpl->get($menu);


echo $menu;

exit;*/

$fileData= new FileData();
$data=$fileData->getFileData('metadata.json');


$tableData= new FileData();
$colWidth=$tableData->getFileData('colums-width.json', ['width:19px;', '','','', 'width:25px;']);

//$tableData->putFileData($colWidth); 
 
 
session_start();


if (isset($_POST['pass']))
{
	$_SESSION['pass']=md5($_POST['pass']);
}
elseif (!empty($_POST) )
{
	/*if ( !isset($_SESSION['pass']) || $_SESSION['pass']!=md5($config['admin']['pass']) ) 
	{
		echo 'Ошибка: нужно залогиниться для выполнения операции.';
		exit();
	}
	*/
	
	if (isset($_POST['colwidth']))
	{
	/*	var_dump($_POST['colwidth']);*/
		
		$tableData->putFileData($_POST['colwidth']);
	
	}
	elseif (isset($_POST['url']))
	{
	
		$url=str_replace($config['admin']['url'], '', $_POST['url']);
		if (!$url) $url='/';	
		
		if (isset($_POST['field']) && $_POST['value'])  // Редактирование
		{
			$data[$url][$_POST['field']]=$_POST['value'];
		}
		elseif (isset($_POST['action']))
		{
			if ($_POST['action']=='del') unset($data[$url]);
		}
		else // добавление
		{
			if (!empty($data)) $order=end($data)['order']+1; else $order=1;
			
			$data[$url]=['title'=>'', 'description'=>'', 'order'=>$order];
			
			/*var_dump($data);*/
		}
		
	}
	elseif (isset($_POST['order']))
	{
		$orderData=$_POST['order'];
			
		$newData=[];
		$order=1;
		foreach ($orderData as $url)
		{
			if (!isset($data[$url])) exit('Ошибка, нет ключа: '.$url);
			
			$newData[$url]=['order'=>$order]+$data[$url];
			$order++;
		}

		$data=$newData;
	}
 
 
	$fileData->putFileData($data);
	
 
	exit('true');
}
 
 
 
$echo='';
$message='';
 
 


if ( !isset($_SESSION['pass']) || $_SESSION['pass']!=md5($config['admin']['pass']) ) 
{
	if (isset($_POST['pass'])) $echo.='<font color="red">Пароль неверный!</font><br><br>';
	
	$echo.='Введите пароль: <form name="form" method="post"><input type="text" name="pass"><input type="submit" value="Отправить"></form>';
	//$echo.='<br>'.$_SERVER['REMOTE_ADDR'];
	//$echo.='<br>'.$_SERVER['HTTP_CF_IPCOUNTRY'];
}
else
{
	$echo.='<table id="seourls" width="100%">
			<thead>
			<tr>
			<th style="width:19px; cursor:pointer;" title="Сортировка">&#8597;</th>
			<th style="'.$colWidth[1].'">Ссылка</th>
			<th style="'.$colWidth[2].'">Title</th>
			<th style="'.$colWidth[3].'">Description</th>
			<th style="width:25px;"></th>
			</tr>
			</thead>
			<tbody>';

	foreach ($data as $url=>$a)
	{
		$rows='<td><span class="order">'.$a['order'].'</span>  <span class="handle">&#8597;</span></td><td>'.$config['admin']['url'].$url.'</td><td><input type="text" value="'.$a['title'].'"></td><td><textarea>'.$a['description'].'</textarea></td><td><div class="del"></div></td>';
		$echo.='<tr class="url" data-id="'.$url.'">'.$rows.'</tr>';
	} 
	
	$echo.='<tr class="newurl" data-id="">
			<td><span class="order"></span>  <span class="handle">&#8597;</span></td><td class="urlvalue"></td><td><input type="text"></td><td><textarea  ></textarea></td><td><div class="del"></div></td>
			</tr>
			</tbody>
			<tfoot>
			<tr class="add" >
			<td colspan="5">Новая ссылка: <input style="width:355px;" id="addurl" type="text" value="'.$config['admin']['url'].'/"> <input type="button" value="Добавить"></td>
			</tr>
			</tfoot>
			</table>';
	
}
?><!DOCTYPE html><html><head><meta charset="utf-8" /><title>Админка управления для проставления мета-тегов</title>
<style type="text/css">
body{  }

table { border-collapse:collapse; }
td { padding:5px; border:#E6E6E6 1px solid; text-indent: 5px; vertical-align:middle}
th { padding:5px; border:#E6E6E6 1px solid;  }

.headmenu { font-size:14pt; }
button { cursor:pointer;}
.red { color:#FF0000; }



.del {
    width: 20px;
    height: 20px;
   /* border-radius: 40px;*/
    position: relative;
    z-index: 1;
    cursor: pointer;
}
.del:before {
    content: '+';
    color: #FF0000;
    position: absolute;
    z-index: 2;
    transform: rotate(45deg);
    font-size: 25px;
	font-weight:bold;
    top: -5px;
    left: 6px;
    transition: all 0.3s cubic-bezier(0.77, 0, 0.2, 0.85);
}
 

.del:hover::after {
content:  " удалить " attr(data-v);
position: absolute; 
white-space:nowrap;
left: 23px; top: 1px; 
z-index: 3;  
background: rgba(255,255,230,0.9);  
font-family: Arial, sans-serif;  
font-size: 11px; 
padding: 3px;  
border: 1px solid #333; 
}


.headmenu a{ font-size:14pt; } 

.bactions
{
border-top:1px #CCCCCC solid;
display: inline-block; 
padding-top:10px;

}

textarea 
{ 
width:95%;
height:70px;
padding:3px;
}

.url input[type=text]
{
width:95%; 
padding:3px;

}
 
input[type=submit]
{
cursor:pointer;
 
}

.handle
{
	position: relative;
    bottom: 2px;
	cursor:move;

}

.newurl { display:none;}

input[type=button]
{
	cursor:pointer;

}
</style>


</head><body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" type="text/javascript"></script>
<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js" integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>
<script src="libs/colResizable/colResizable.min.js" type="text/javascript"></script> 
  

<?PHP echo $echo; ?>


<br>
 

<textarea id="excel"></textarea>

<script type="text/javascript">
$(function(){	

	var tableId='TableData';
	
	var onSampleResized = function(e){  	
	
		let columns = $(e.currentTarget).find("th");
		let send={ 'colwidth': []};
 
		columns.each(function()
		{ 		
			send.colwidth.push("width:"+$(this).width()+"px;");
			console.log($(this).width());  		
		});
 		
		ajaxSuccessFunc=function() {
			console.log('Новые размеры колонок таблицы сохранены');
		};
		
		postRequest(send);	
	}; 
	
	$("#"+tableId).colResizable({
		liveDrag:true, 
		gripInnerHtml:"<div class='grip'></div>", 
		draggingClass:"dragging", 
		resizeMode:'fit',
		onResize : onSampleResized
	
	});
	
});	

/*При перемещении*/
/*
var fixHelper = function(e, ui) {

	console.log(ui.attr('data-id'));
	
	ui.children().each(function() {
		$(this).width($(this).width());
	});
	return ui;
};	
*/
 
 
var updateMove= function(e, ui) {

	console.log(e);
	
	updateOrder();
};	
 
 
$('table tbody').sortable({
	update: updateMove,
	/*helper: fixHelper,*/
	cursor: "move",
	axis: "y",
	revert: true,
	handle: '.handle'
});



var editId;
var editField;
var startOrder=1;
var ajaxSuccessFunc=function() {
	
		console.log('данные обновлены');
	};

function waitAjax(wait)
{
	var selector='body,input,textarea';
	if (wait) $(selector).css('cursor', 'wait'); else $(selector).css('cursor', '');
	
	//if (!success) $('*').css('cursor', 'wait'); else	 $('*').css('cursor', '');
}


function updateOrder()
{
	let order=startOrder;
	let send={ 'order': []};
	let dataId;
	
	$("table .url").each(function(inf){
		
		dataId=$(this).attr("data-id");
		/*console.log(inf + ': '+ order + ': '+ dataId);*/
		send.order.push(dataId);
		$(this).find('.order').html(order);
		order++;
	
	});
	
	/*console.log(send);*/
	
	ajaxSuccessFunc=function() {
	
		console.log('Сортировка завершена');
	};
	
 	if (order>1) postRequest(send);	
}


function updateMeta(url, element)
{
	let field;
	let tag=element.prop('nodeName'); 
	
	if (tag=='TEXTAREA') field='description'; else if(tag=='INPUT') field='title'; else return false;
	
	let val=element.val().trim();
	
	if (val)
	{
		let data={ url: url, value: element.val(), field: field };
		
		
		ajaxSuccessFunc=function() {
		
			console.log('Обновление значения ' +field + ' завершено');
		};
		
		postRequest(data);
	}
}


function postRequest(send)
{
	waitAjax(true); 
	
	$.post( "admin.php?",  send,  function( data ) {
	
		if (data!=='true') alert( data ); // ошибка
		else 
		{
			waitAjax(false);		
			
			ajaxSuccessFunc();
		}
		 
	}).fail(function() {
	  
	 	alert( "Нет доступа к админке, проверьте подключение Интернета" );
	});	
}


function getClipboard(e)
{
  e.preventDefault();
  
  return window.event.clipboardData.getData('text'); 
}



$("tbody").on('click', '.del', function(e) {
	
	console.log('удаление');
	let row=$(this).closest("tr");
	let delurl=row.attr("data-id");
	let send={ 'url' : delurl, 'action' : 'del' };
			
	ajaxSuccessFunc=function() {
	
		row.remove();
		updateOrder();
	};	
	
	postRequest(send);
});



$(".add input[type=button]").click(function(e) {
	
	let addurl=$('#addurl').val();
	let url= new URL(addurl);
	let uri=url.pathname;
	let send = { 'url' : uri };
	
	console.log(uri);
	
	ajaxSuccessFunc=function() {
		let clone=$('.newurl').clone();
		clone.attr('data-id', uri); 
		clone.toggleClass("newurl url");
		$('.newurl').before(clone);
		$('.urlvalue').html(addurl);
		$('.url .urlvalue').removeClass();
		
		console.log('страрт сортировки');
		updateOrder();
	};	
	
	console.log('добавление post');
	postRequest(send);
});


$("tbody").on('keypress blur', "input[type=text],textarea", function(e) {

	// console.log(e);
	
	if(e.which == 13) 
	{
		$(this).blur();		
    } 
	else if (e.type=="focusout")
	{
		var url=$(this).closest("tr").attr("data-id");
 		console.log(url);
		
		updateMeta(url, $(this));  
	}

});

/*
Проверить порядок.

*/


/*
$( "input[type=text],textarea" ).click(function(e) {

 var id=$(this).closest("tr").attr("id");
 console.log(id);

});
 

*/

$(document).on('paste', '#excel', function(event) {
   
  var data =  getClipboard(event);
   
  console.log(data);
});

 
 
$("#excel").on( 'click', function() {
  
	data=$(this).val(); 
	$.each(data.split("\n"), function(l, line) {
	
		$.each(line.split("\t"), function(r, row) {
			// сплитуем по столбцам
				 
				console.log(row);
		});
	});

});

 

</script>
</body></html>
