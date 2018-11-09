<?php

class Controller_users extends Controller {

    function __construct() {
		if ($_SESSION['role'] != 1) {
			header("Location: /profile");
			exit();
		}
	}

	public function search() {
		$user_model = Controller::loadModel("user");
		$page = isset($_REQUEST['page']) ? (int) $_REQUEST['page'] : 1;
		$limit = isset($_REQUEST['limit']) ? (int) $_REQUEST['limit'] : 10;
		$search_field = isset($_REQUEST['field']) ? $_REQUEST['field'] : '';
		$search_text = isset($_REQUEST['text']) ? $_REQUEST['text'] : '';
		$user_model->setFilter($search_field, $search_text);
		$pagin = new Paginator($user_model->countUsers(), $limit);
		$pagin->selectPage($page);
		$users = $user_model->selectFilteredUsers($limit, $pagin->getOffset());

		$data['error'] = $this->error;
		$data['users'] = $users;
		$data['delete'] = 'users/delete?id=';
		$data['pagin'] = $pagin->getData();
		$data['pages'] = $pagin->getPages();
		$data['search_field'] = $search_field;
		$data['search_text'] = $search_text;

		$users_table_view = new View("users_table");

		$users_table_view->setData($data);

		$users_table_view->display();
	}

	public function delete() {
		$user_model = Controller::loadModel("user");

		$data['result_text'] = "Fail to delete user.";

		if (isset($_REQUEST["id"])) {
			if ($_REQUEST["id"] == $_SESSION['id']) {
				$data['result_text'] = 'Can\'t to delete himself';
				$result_view = new View("fail");
			} else {
				if ($user_model->deleteUser($_REQUEST["id"])) {
					$result_view = new View("success");
					$data['result_text'] = "User was succesfully deleted.";
				} else {
					$result_view = new View("fail");
				}
			}
		} else {
			$result_view = new View("fail");
			$data['result_text'] = "Can't delete this user.";
		}

		$header_view = new View("header");
		$footer_view = new View("footer");

		$header['title'] = "Profile info";
		$data['link_url'] = "/users";
		$data['link_text'] = "View users";

		$header_view->setData($header);
		$result_view->setData($data);

		$header_view->display();
		$result_view->display();
		$footer_view->display();
	}

	public function load_users() {
        $user_model = Controller::loadModel("user");

	    $csv = array();

        if ($_FILES['csv']['error'] == 0) {
            $ext = strtolower(end(explode('.', $_FILES['csv']['name'])));
            //$type = $_FILES['csv']['type'];
            $tmpName = $_FILES['csv']['tmp_name'];

            if($ext === 'csv'){
                if(($handle = fopen($tmpName, 'r')) !== FALSE) {
                    set_time_limit(0);

                    $row = 0;
                    $csv_fail = 0;
                    $csv_success = 0;

                    while(($csv_row = fgetcsv($handle, 1000, ';')) !== FALSE) {
                        $col_count = count($csv_row);

                        if($col_count == 5){
                            $csv[$row]['login'] = $csv_row[2];

                            $userInfo = array(
                                'login' => addslashes($csv_row[2]),
                                'first_name' => addslashes($csv_row[0]),
                                'last_name' => addslashes($csv_row[1]),
                                'email' => addslashes($csv_row[3]),
                                'password' => md5($csv_row[4]),
                                'role' => 0);

                            if (empty($this->error)) {
                                if ($user_model->addNewUser($userInfo)) {
                                    $csv[$row]['load_error'] = '';
                                    $csv_success++;
                                } else {
                                    $csv[$row]['load_error'] = $user_model->getLastError();
                                    $csv_fail++;
                                }
                            }
                        }
                        else {
                            $csv[$row]['load_error'] = 'incomplete data in the row: '.$row;
                        }
                        $row++;
                    }
                    fclose($handle);

                    //print_r($csv);
                    $data['csv'] = $csv;
                    $data['csv_name'] = $_FILES['csv']['name'];
                    $data['csv_fail_count'] = $csv_fail;
                    $data['csv_success_count'] = $csv_success;

                    $users_load_view = new View("users_load");
                    $header_view = new View("header");
                    $footer_view = new View("footer");

                    $header['title'] = "Result info";
                    $data['link_url'] = "/users";
                    $data['link_text'] = "View users";

                    $header_view->setData($header);
                    $users_load_view->setData($data);

                    $header_view->display();
                    $users_load_view->display();
                    $footer_view->display();
                }
            }
        }
    }

    public function users_random(){
        $user_model = Controller::loadModel("user");
        $random_offset = random_int(1, $user_model->countUsers());
        $users_random = $user_model->selectFilteredUsers(1, $random_offset);
        $users_random_view = new View("random_text");
        $first_name=$users_random[0]['first_name'];
        $last_name=$users_random[0]['last_name'];
        $id=$users_random[0]['id'];
        $users_random_view->setData($first_name);
        $users_random_view->setData($last_name);
        $users_random_view->setData($id);
        $users_random_view->display();
    }
    public function index() {

		if (isset($_REQUEST['limit'])) {
			$limit = $_REQUEST['limit'];
		}

		$header['title'] = "Profile info";
		$users['title'] = "View users";

		$header_view = new View("header");
		$users_view = new View("users");
		$footer_view = new View("footer");
        $users_load_view = new View("users_load");
        $users_random_view = new View("users_random");

		$header_view->setData($header);
		$users_view->setData($users);

		$header_view->display();
		$users_view->display();
		$this->search();

        $users_load_view->display();

        $users_random_view->display();

		$footer_view->display();
	}
}
