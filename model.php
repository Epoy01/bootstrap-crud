<?php

class model{
	private $host="localhost";
	private $user="root";
	private $db="clifford_db";
	private $pass="";
	private $conn=null;
	private $limit=5;
	private $rowCount=0;
	private $cur_page=1;

	public function __construct(){
		try{
			$this->conn=new PDO("mysql:host={$this->host};dbname={$this->db}",$this->user,$this->pass);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		}catch(PDOException $err_msg){
			echo $err_msg;
		}
		if(isset($_REQUEST['page']) && !empty($_REQUEST['page'])){
			$this->cur_page=$_REQUEST['page'];
		}
	}

	public function showData($table,$cond){
		try{
			$offset=0;
			$offset=($this->cur_page*$this->limit)-$this->limit;
			$search=null;
			$wheres='';

			if(isset($_REQUEST['search']) && isset($_REQUEST['search_text']) && !empty($_REQUEST['search_text'])){
				$search=$_REQUEST['search_text'];
				$wheres=$cond['search'];

			}else{
				$wheres=$cond['show'];
			}

			$sql="SELECT {$cond['select']} FROM {$table} {$wheres} LIMIT {$this->limit} OFFSET {$offset}";
			$q=$this->conn->query($sql) or die("Query Failed");
			
			$data=[];
			if($q->rowCount()>0){
				$this->rowCount=$this->getAllPages($table,$wheres);

				while($result = $q->fetch(PDO::FETCH_ASSOC)){
					$data['result'][]=$result;
				}
			}else{
				$data['result']=[];
				$data['msg']="No Records Found";
			}
			return $data;

		}catch(PDOException $err_msg){
			echo $err_msg;
		}
	}

	public function getById($id,$table){
		try{
			$sql="SELECT * FROM {$table} WHERE u_id=:id";
			$q=$this->conn->prepare($sql) or die("Query Failed");
			$q->execute(array('id'=>$id)) or die("Query Failed");
			$data=[];

			if($q->rowCount()>0){
				$data=$q->fetch(PDO::FETCH_ASSOC);
			}
			return $data;

		}catch(PDOException $err_msg){
			echo $err_msg;
		}
	}

	public function insert($id,$fname,$mname,$lname,$table){
		try{
			$sql="INSERT INTO {$table} SET u_id=:id,fname=:fname,mname=:mname,lname=:lname";
			$q=$this->conn->prepare($sql) or die("Query Failed");
			if($q->execute(array('id'=>$id,'fname'=>$fname,'mname'=>$mname,'lname'=>$lname)) or die("Query Failed")){
				return true;
			}else{
				return false;
			}

		}catch(PDOException $err_msg){
			echo $err_msg;
		}
	}

	public function update($id,$fname,$mname,$lname,$table){
		try{
			$sql="UPDATE {$table} SET fname=:fname,mname=:mname,lname=:lname WHERE u_id=:id";
			$q=$this->conn->prepare($sql) or die("Query Failed");
			if($q->execute(array('id'=>$id,'fname'=>$fname,'mname'=>$mname,'lname'=>$lname)) or die("Query Failed")){
				return true;
			}else{
				return false;
			}

		}catch(PDOException $err_msg){
			echo $err_msg;
		}
	}

	public function delete($id,$table){
		try{
			$sql="DELETE FROM {$table} WHERE u_id=:id";
			$q=$this->conn->prepare($sql) or die("Query Failed");
			if($q->execute(array('id'=>$id)) or die("Query Failed")){
				return true;
			}else{
				return false;
			}

		}catch(PDOException $err_msg){
			echo $err_msg;
		}
	}
	
	public function register($fname,$mname,$lname,$email,$pwd,$c_pwd,$table){
		try{

			$sql_emailCheck="SELECT * FROM {$table} WHERE email=:email";
			$q_emailCheck=$this->conn->prepare($sql_emailCheck) or die("Query Failed");
			$q_emailCheck->execute(array('email'=>$email)) or die("Query Failed");
			$data=[];
			$data['msg']=[];
			$data['success']=true;
			if($q_emailCheck->rowCount()>0){
				$data['msg']['email']="Email Already Exist";
				$data['success']=false;
			}
			if($pwd!==$c_pwd){
				$data['msg']['pwd']="Password Did Not Match";
				$data['success']=false;
			}

			if($data['success']){
				$pwd=md5($pwd);
				$sql="INSERT INTO {$table} SET fname=:fname,mname=:mname,lname=:lname,email=:email,pwd=:pwd";
				$q=$this->conn->prepare($sql) or die("Query Failed");
				if($q->execute(array('fname'=>$fname,'mname'=>$mname,'lname'=>$lname,'email'=>$email,'pwd'=>$pwd)) or die("Query Failed")){
					$data['success']=true;
				}else{
					$data['success']=false;
				}
			}
			return $data;
		}catch(PDOException $err_msg){
			echo $err_msg;
		}
	}

	public function login($email,$pwd,$table){
		try{
			$pwd=md5($pwd);
			$sql="SELECT * FROM {$table} WHERE email=:email AND pwd=:pwd";
			$q=$this->conn->prepare($sql) or die("Query Failed");
			$q->execute(array('email'=>$email,'pwd'=>$pwd)) or die("Query Failed");
			$data=[];
			$data['msg']="";
			$data['success']=true;

			if($q->rowCount()<=0){
				$data['msg']="Invalid Email or Password!";
				$data['success']=false;
			}else{
				$data['msg']="";
				$data['success']=true;

				session_start();
				$_SESSION['login']=true;
				$_SESSION['email']=$email;
			}
			return $data;
		}catch(PDOException $err_msg){
			echo $err_msg;
		}
	}

	public function getAllPages($table,$wheres){
		try{
			$sql="SELECT * FROM {$table} {$wheres}";
			$q=$this->conn->query($sql) or die("Query Failed");
			if($q->rowCount()>0){
				return $q->rowCount();
			}
		}catch(PDOException $msg){
			echo $msg;
		}
	}
	public function paginate($table,$cond){
		$wheres='';
		$url_params='';

		if(isset($_REQUEST['search']) && isset($_REQUEST['search_text']) && !empty($_REQUEST['search_text'])){
			$search=$_REQUEST['search_text'];
			$wheres=$cond['search'];
			$url_params="&search={$_REQUEST['search']}&search_text={$_REQUEST['search_text']}";
		}else{
			$wheres=$cond['show'];
			$url_params="";
		}

		$this->getAllPages($table,$wheres);

		$all=$this->rowCount;
		$pages=ceil($all/$this->limit);
		$paginate_pages=null;
		$next_btn=null;
		$prev_btn=null;

		if($this->cur_page<=1){
			$prev_btn="disabled";
		}

		if($this->cur_page>=$pages){
			$next_btn="disabled";
		}

		$prev=$this->cur_page-1;
		$next=$this->cur_page+1;

		$paginate_pages=$paginate_pages."
		<li class='page-item {$prev_btn}' aria-disabled='true' aria-label='First'>
		<a class='page-link' href='{$_SERVER['PHP_SELF']}?page=1{$url_params}'>First</a>
		</li>
		<li class='page-item {$prev_btn}' aria-disabled='true' aria-label='« Previous'>
		<a class='page-link' href='{$_SERVER['PHP_SELF']}?page={$prev}}{$url_params}'>‹</a>
		</li>";

		for ($page=1; $page <= $pages ; $page++) {
			$active="page-item";
			if($page==$this->cur_page){
				$active="page-item active";
				$paginate_pages=$paginate_pages."<li class='page-item active' aria-current='page'><span class='page-link'>{$page}</span></li>";
			}else{
				$paginate_pages=$paginate_pages."<li class='page-item {$active}'><a class='page-link' href='{$_SERVER['PHP_SELF']}?page={$page}{$url_params}'>{$page}</a></li>";
			}

			
		}

		$paginate_pages=$paginate_pages."
		<li class='page-item {$next_btn}' aria-disabled='true' aria-label='Next »'>
		<a class='page-link' href='{$_SERVER['PHP_SELF']}?page={$next}{$url_params}'>›</a>
		</li>
		<li class='page-item {$next_btn}' aria-disabled='true' aria-label='Last'>
		<a class='page-link' href='{$_SERVER['PHP_SELF']}?page={$pages}{$url_params}'>Last</a>
		</li>";

		if($pages>1){
			echo $paginate_pages;	
		}
		

	}
}

?>