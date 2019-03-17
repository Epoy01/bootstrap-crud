<?php
include_once("header.php");
session_start();
if(!isset($_SESSION['login'])&&!isset($_SESSION['email'])){
	header("Location:login.php");
}

if(isset($_REQUEST['search']) && isset($_REQUEST['search_text']) && !empty($_REQUEST['search_text'])){

	$cond['search']="WHERE u_id LIKE'%{$_REQUEST['search_text']}%' 
					OR fname LIKE'%{$_REQUEST['search_text']}%'
					OR mname LIKE'%{$_REQUEST['search_text']}%'
					OR lname LIKE'%{$_REQUEST['search_text']}%' ";

}else{
	$cond['show']='';
}

$cond['select']='*';

	

?>
<body>
<div>

	<div class="row">
		<div class="col-sm"></div>
		<div class="col-sm-2">
			<a href="logout.php" class="pull-right">Log Out</a>
		</div>
	</div>

<div class="container">
<br/>


<?php
if(isset($_REQUEST['success'])){
echo <<<show
	<div class="alert alert-success alert-dismissible" id="myAlert">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <strong>{$_REQUEST['success']}</strong>
  	</div>
    <br />
show;
}
?>
    <h1>Contacts</h1><hr/>
  </div>

  <div class="container">
	  <div class="form-group">
	    <form action="index.php" method="get">
	      <div class="input-group">
	        <a href="insert.php" class="btn btn-success ">Add New Record</a>
	        <span class="col-sm-4"></span>
	        <input  class="form-control" name="search_text" type="text" value="<?php echo isset($_GET['search_text']) ? $_GET['search_text']:'' ?>" />
	        <div class="input-group-prepend">
	           <input class="form-control btn btn-primary" type="submit" name="search" value="Search" />
	        </div>

	      </div>
	    </form>
	  </div>  	
  </div>

  <div class="container">
    <table class="table table-striped table-bordered">
      <thead class="table-primary">
          <tr>
            <td>ID</td>
            <td>First Name</td>
            <td>Middle Name</td>
            <td>Last Name</td>
            <td>Action</td>
          </tr>
      </thead>
      <tbody>
<?php
$data=$model->showData("user",$cond);
foreach($data['result'] as $result){
extract($result);

echo <<<show
          <tr>
              <td>{$u_id}</td>
              <td>{$fname}</td>
              <td>{$mname}</td>
              <td >{$lname}</td>
              <td>
                  <a href="edit.php?uid=$u_id" class="btn btn-primary">Edit</a>
                  <a href="delete.php?uid=$u_id" class="btn btn-danger">Delete</a>
                  <!-- <button class="btn btn-danger" type="submit">Delete</button> -->
              </td>
          </tr>
show;
}

if(isset($data['msg'])){
echo <<<show
          <tr>
              <td colspan="5" class="table-danger" align="center">
                {$data['msg']}
              </td>
          </tr>	
show;
}
              
?>
      </tbody>
    </table>
	<div>
		<ul class="pagination" role="navigation">
			<?php
				$model->paginate("user",$cond);
			?>
		</ul>
	</div>
  </div>
<div><br/>
</body>
</html>


