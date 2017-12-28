<?php
namespace Pedetes\install;

use \PDO;

class install {

	public function install($ctn) {
	    $config =$ctn['config']->getData();
		if(!$config['installed']) {
			$msg = "";

			// no sec here, just install
			if($_REQUEST['action']=='save') {
				$file = $ctn['pathApp']."config.json";

				// update DB settings
				$config['database']['name'] = $_REQUEST['db_name'];
				$config['database']['user'] = $_REQUEST['db_user'];
				$config['database']['pass'] = $_REQUEST['db_pass'];
				$config['database']['host'] = $_REQUEST['db_host'];
				$config['database']['port'] = $_REQUEST['db_port'];
				unset($config['database']['nodatabase']);

				// set basic data
				$config['url'] = $_REQUEST['url'];
				$config['site'] = preg_replace("/[^0-9a-zA-Z_\s]+/", "", $_REQUEST['url']);
				$config['salt'] = md5(time().rand(10, 20));

				// connect to database (with new parameters)
				try {
					$db = new pdo('mysql:host='.$_REQUEST['db_host'].':'.$_REQUEST['db_port'].';dbname='.$_REQUEST['db_name'], $_REQUEST['db_user'], $_REQUEST['db_pass']);

					// install mysql structure
					$structureFile = $ctn['pathApp']."database.sql";
					if(!file_exists($structureFile)) {
						$msg = "Could not fine database file";
					} else {
						$cmd = file_get_contents($structureFile);
						$sth = $db->prepare($cmd);
						$sth->execute();

						// create user
						$sth = $db->prepare("INSERT INTO user (role_id,username,password) VALUES (3,:user,:pass); ");
						$sth->bindValue("user", $_REQUEST['user']);
						$sth->bindValue("pass", password_hash($_REQUEST['pass'].$config['salt'], PASSWORD_BCRYPT, array('cost' => 12)));
						$sth->execute();

						$config['installed'] = true;
						$json = json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
						file_put_contents($file,$json);
						apcu_clear_cache();
						header("Location: /");
					}
				} catch (\PDOException $e) {
					$msg = $e->getMessage();
				}

			}


			?>
				<form action="/">
				<input type="hidden" name="action" value="save" />
				<h1>Install</h1>
				<h2>Website Settings</h2>
				<table>
					<tr>
						<td>URL:</td>
						<td><input type="text" name="url" value="<?php echo $_REQUEST['url']?>" /></td>
					</tr>
					<tr>
						<td>Username:</td>
						<td><input type="text" name="user" value="<?php echo $_REQUEST['user']?>" /></td>
					</tr>
					<tr>
						<td>Password:</td>
						<td><input type="password" name="pass" value="<?php echo $_REQUEST['pass']?>" /></td>
					</tr>
				</table>
				<h2>Database Settings</h2>
				<p style='color: red;'><?php echo $msg?></p>
				<table>
					<tr>
						<td>DB-Name:</td>
						<td><input type="text" name="db_name" value="<?php echo $_REQUEST['db_name']?>" /></td>
					</tr>
					<tr>
						<td>Username:</td>
						<td><input type="text" name="db_user" value="<?php echo $_REQUEST['db_user']?>" /></td>
					</tr>
					<tr>
						<td>Password:</td>
						<td><input type="password" name="db_pass" value="<?php echo $_REQUEST['db_pass']?>" /></td>
					</tr>
					<tr>
						<td>Host:</td>
						<td><input type="text" name="db_host" value="<?php echo $_REQUEST['db_host']?>" /></td>
					</tr>
					<tr>
						<td>Port:</td>
						<td><input type="text" name="db_port" value="<?php echo $_REQUEST['db_port']?>" /></td>
					</tr>
				</table>
				<p>
					<input type="submit" value="Install">
				</p>
				</form>
			<?php
			die();
		}
	}


}
