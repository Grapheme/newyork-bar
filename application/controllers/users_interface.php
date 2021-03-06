<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Users_interface extends CI_Controller {
	
	var $user = array('uid'=>0,'ulogin'=>'','uemail'=>'');
	var $loginstatus = array('status'=>FALSE);
	var $months = array("01"=>"января","02"=>"февраля","03"=>"марта","04"=>"апреля","05"=>"мая","06"=>"июня","07"=>"июля","08"=>"августа","09"=>"сентября","10"=>"октября","11"=>"ноября","12"=>"декабря");
	
	function __construct(){
		
		parent::__construct();
		$this->load->model('mdusers');
		$this->load->model('mdfoodcategory');
		$this->load->model('mdfoods');
		$this->load->model('mdrssevents');
		$this->load->model('mdtextblock');
		
		$cookieuid = $this->session->userdata('logon');
		if(isset($cookieuid) and !empty($cookieuid)):
			$this->user['uid'] = $this->session->userdata('userid');
			if($this->user['uid']):
				$userinfo = $this->mdusers->read_record($this->user['uid']);
				if($userinfo):
					$this->user['ulogin'] 			= $userinfo['login'];
					$this->user['uemail'] 			= '';
					$this->user['utype'] 			= $this->session->userdata('utype');
					$this->loginstatus['status'] 	= TRUE;
				endif;
			endif;
			
			if($this->session->userdata('logon') != md5($userinfo['login'])):
				$this->loginstatus['status'] = FALSE;
				$this->user = array();
			endif;
		endif;
	}
	
	public function index(){
		
		$pagevar = array(
			'title'			=> 'Снять банкетный зал недорого на 50-100 человек, ресторан на юбилей, для свадьбы, корпоратива, выпускного Ростов.',
			'description'	=> 'Ресторан-бар «Нью-Йорк» предлагает снять банкетный зал недорого на 50-100 человек. Наш ресторан идеальное место для проведения юбилея, свадьбы, корпоратива, выпускного в Ростове-на-Дону.',
			'author'		=> '',
			'baseurl' 		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'msgs'			=> $this->session->userdata('msgs'),
			'msgr'			=> $this->session->userdata('msgr'),
		);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('submit')):
			$_POST['submit'] = NULL;
			$this->form_validation->set_rules('email',' ','required|valid_email|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка. Повторите ввод.');
			else:
				ob_start();
				?>
				<p>
					Здравствуйте.<br/>Вы подписались на анонс событий от ресторана-бара New York.<br/>
					<br/><br/>
					Будь первым кто узнает о новом событии.<br/>
					Только одно письмо в месяц. Всегда купон на скидку.<br/>
				</p>
				<?
				$mailtext = ob_get_clean();
				
				$this->email->clear(TRUE);
				$config['smtp_host'] = 'localhost';
				$config['charset'] = 'utf-8';
				$config['wordwrap'] = TRUE;
				$config['mailtype'] = 'html';
				
				$this->email->initialize($config);
				$this->email->to($_POST['email']);
				$this->email->from('info@newyork-bar.ru','Ресторан-бар Нью-Йорк');
				$this->email->bcc('');
				$this->email->subject('Анонс событий от New York');
				$this->email->message($mailtext);	
				$this->email->send();
				
				$this->mdrssevents->insert_record($_POST);
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		$this->load->view("users_interface/index",$pagevar);
	}

	public function en(){
		
		$pagevar = array(
			'title'			=> 'New York Restaurant & Bar 40 line',
			'description'	=> 'Restaurant-bar New York offers services in organizing banquets, receptions, weddings, corporate parties and anniversaries.',
			'author'		=> '',
			'baseurl' 		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'msgs'			=> $this->session->userdata('msgs'),
			'msgr'			=> $this->session->userdata('msgr'),
		);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$this->load->view("users_interface/en",$pagevar);
	}
	
	public function about(){
		
		$pagevar = array(
			'title'			=> 'Ресторан-бар Нью-Йорк :: Информация о ресторане :: Отзывы посетителей',
			'description'	=> 'Ресторан-бар New York отличает оригинальный дизайн интерьера и разнообразная кухня. Здесь можно отдохнуть от ростовской суеты и окунуться в теплую атмосферу вечернего Нью-Йорка. Просторный и концептуальный зал, оснащенный современной звуковой, видео- и световой аппаратурой. Услуги проведения банкетов и торжеств. Банкетный зал на 80 человек.',
			'author'		=> '',			
			'baseurl' 		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'textblock'		=> $this->mdtextblock->read_field(2,'textblock'),
			'msgs'			=> $this->session->userdata('msgs'),
			'msgr'			=> $this->session->userdata('msgr'),
		);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$this->load->view("users_interface/about",$pagevar);
	}
	
	public function menu(){
		
		if($this->uri->uri_string() == 'menu'):
			redirect('menu/'.$this->mdfoodcategory->read_field(5,'uri'));
		else:
			$ct = $this->mdfoodcategory->search_category('uri',$this->uri->segment(2));
		endif;
		
		$pagevar = array(
			'title'			=> 'Меню блюд лучшего ресторана Ростова с ценами. Меню банкетного зала и блюда детского меню. Бизнес ланч в Ростове.',
			'description'	=> 'Предлагаем ознакомиться с меню блюд самого гостеприимного, если не лучшего ресторана Ростова-на-Дону «Нью-Йорк» с ценами. Меню банкетного зала и блюда детского меню разнообразны и недороги. Наш бизнес ланч в Ростове один из самых вкусных.',
			'author'		=> '',
			'baseurl' 		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'fcategory'		=> $this->mdfoodcategory->read_records(),
			'foods'			=> array(),
			'ctgtitle'		=> $ct['title'],
			'textblock'		=> $this->mdtextblock->read_field(1,'textblock'),
		);
		
		if($foods = $this->mdfoods->read_records($ct['id'],'subcategory ASC,title ASC')):
			$food_category1 = array();
			$food_category2 = array();
			foreach($foods as $food_key => $food_value):
				if(empty($food_value['subcategory'])):
					$food_value['subcategory'] = 'пусто';
				endif;
				$food_category1[$food_value['subcategory']][] = $food_value;
			endforeach;
			foreach($food_category1 as $food_key => $food_value):
				foreach($food_value as $food_category_key => $food_category_value):
					if(empty($food_category_value['subcategory1'])):
						$food_category_value['subcategory1'] = 'пусто';
					endif;
					$food_category2[$food_key][$food_category_value['subcategory1']][] = $food_category_value;
				endforeach;
			endforeach;
			$pagevar['foods'] = $food_category2;
		endif;
		
		
		$this->load->view("users_interface/menu",$pagevar);
	}
	
	public function afisha(){
		
		$pagevar = array(
			'title'			=> 'Ресторан-бар Нью-Йорк :: Джазовые вечера :: Спортивные трансляции :: Живая музыка ',
			'description'	=> 'Ресторан-бар New York отличает оригинальный дизайн интерьера и разнообразная кухня. Здесь можно отдохнуть от ростовской суеты и окунуться в теплую атмосферу вечернего Нью-Йорка. Вы можете насладиться живой музыкой или посмотреть спортивные трансляции.',
			'author'		=> '',
			'baseurl' 		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'msgs'			=> $this->session->userdata('msgs'),
			'msgr'			=> $this->session->userdata('msgr')
		);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$this->load->view("users_interface/afisha",$pagevar);
	}

	public function banketi(){
		
		$pagevar = array(
			'title'			=> 'Лучшие банкетные залы. Аренда банкетного зала, ресторана  для свадьбы, юбилея недорого. Стоимость и цены низкие для Ростова.',
			'description'	=> 'Ресторан-бар «Нью-Йорк» славится лучшим банкетным залом. Аренда банкетного зала, ресторана  для свадьбы, юбилея, корпоратива, выпускного  и других торжеств обойдется вам недорого. Стоимость и цены праздника у нас низкие для Ростова.',
			'author'		=> '',
			'baseurl' 		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'msgs'			=> $this->session->userdata('msgs'),
			'msgr'			=> $this->session->userdata('msgr')
		);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$this->load->view("users_interface/banketi",$pagevar);
	}
	
	public function banketi_svadbi(){
		
		$pagevar = array(
			'title'			=> 'Свадебный банкет – где провести свадебный банкет или недорого отметить свадьбу в Ростове? Ресторан Ростова-на-Дону для свадьбы.',
			'description'	=> 'Если у вас намечается свадебный банкет и стал вопрос: где провести свадебный банкет или недорого отметить свадьбу в Ростове, звоните нам. «Нью-Йорк»  - уютный, с хорошей кухней ресторан Ростова-на-Дону для свадьбы.',
			'author'		=> '',
			'baseurl' 		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'msgs'			=> $this->session->userdata('msgs'),
			'msgr'			=> $this->session->userdata('msgr')
		);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$this->load->view("users_interface/banketi_svadbi",$pagevar);
	}	

	public function banketi_birthday(){
		
		$pagevar = array(
			'title'			=> 'День рождения в Ростове-на-Дону. Где отметить юбилей, детский день рождения ребенка недорого в Ростове?',
			'description'	=> 'Где отметить недорого, но ярко и оригинально день рождения свое, родителей, ребенка или юбилей в Ростове? Конечно же, отпраздновать день рождения в Ростове-на-Дону лучше всего в ресторане «Нью-Йорк».',
			'author'		=> '',
			'baseurl' 		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'msgs'			=> $this->session->userdata('msgs'),
			'msgr'			=> $this->session->userdata('msgr')
		);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$this->load->view("users_interface/banketi_birthday",$pagevar);
	}

	public function banketi_korporativi(){
		
		$pagevar = array(
			'title'			=> 'Корпоратив Ростов. Проведение корпоративных вечеров – корпоративов или где вкусно поесть в Ростове-на-Дону.',
			'description'	=> 'Провести корпоратив в Ростове-на-Дону предлагаем в ресторане «Нью-Йорк». Мы специалисты в проведении корпоративных вечеров  или корпоративов и знаем, что главное для гостей вкусно поесть и повеселиться от души, чтобы знал и завидовал весь Ростов-на-Дону.',
			'author'		=> '',
			'baseurl' 		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'msgs'			=> $this->session->userdata('msgs'),
			'msgr'			=> $this->session->userdata('msgr')
		);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$this->load->view("users_interface/banketi_korporativi",$pagevar);
	}
	
	public function akcii(){
		
		$pagevar = array(
			'title'			=> 'Ресторан-бар Нью-Йорк :: Акции и специальные предложения',
			'description'	=> 'С сегодняшнего дня стартуют новые заманчивые акции для наших завсегдатаев дорогих нам  посетителей.',
			'author'		=> '',
			'baseurl' 		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'msgs'			=> $this->session->userdata('msgs'),
			'msgr'			=> $this->session->userdata('msgr')
		);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$this->load->view("users_interface/akcii",$pagevar);
	}
	
	public function contacts(){
		
		$pagevar = array(
			'title'			=> 'Ресторан-бар Нью-Йорк :: Контактная информация',
			'description'	=> 'Ресторан-бар New York расположен недалеко от центра города и предлагает своим посетителям возможность уехать из ресторана на оплачиваемом такси.',
			'author'		=> '',
			'baseurl' 		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'msgs'			=> $this->session->userdata('msgs'),
			'msgr'			=> $this->session->userdata('msgr')
		);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('submit')):
			$_POST['submit'] = NULL;
			$this->form_validation->set_rules('name',' ','required|trim');
			$this->form_validation->set_rules('email',' ','required|valid_email|trim');
			$this->form_validation->set_rules('phone',' ','required|trim');
			$this->form_validation->set_rules('text',' ','required|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка. Повторите ввод.');
			else:
				ob_start();
				?>
				<p>Сообщение от <?=$_POST['name'];?></p>
				<p>Email <?=$_POST['email'];?></p>
				<p>Телефон <?=$_POST['phone'];?></p>
				<p>
					<?=$_POST['text'];?>
				</p>
				<?
				$mailtext = ob_get_clean();
				
				$this->email->clear(TRUE);
				$config['smtp_host'] = 'localhost';
				$config['charset'] = 'utf-8';
				$config['wordwrap'] = TRUE;
				$config['mailtype'] = 'html';
				
				$this->email->initialize($config);
				$this->email->to('info@newyork-bar.ru');
				$this->email->from($_POST['email'],$_POST['name']);
				$this->email->bcc('');
				$this->email->subject('Форма обратной связи Newyork Bar');
				$this->email->message($mailtext);	
				$this->email->send();
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		$this->load->view("users_interface/contacts",$pagevar);
	}
	
	public function vakansii(){
		
		$pagevar = array(
			'title'			=> 'Ресторан-бар Нью-Йорк :: Контактная информация',
			'description'	=> 'Ресторан-бар New York',
			'author'		=> '',
			'baseurl' 		=> base_url(),
			'loginstatus'	=> $this->loginstatus,
			'userinfo'		=> $this->user,
			'msgs'			=> $this->session->userdata('msgs'),
			'msgr'			=> $this->session->userdata('msgr')
		);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		$this->load->view("users_interface/vakansii",$pagevar);
	}
	
	public function admin_login(){
	
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> 'Панель администрирования',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus['status'],
					'userinfo'		=> $this->user,
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr'),
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('submit')):
			$_POST['submit'] == NULL;
			$userinfo = $this->mdusers->auth_user($this->input->post('login'),$this->input->post('password'));
			if(!$userinfo):
				$this->session->set_userdata('msgr','Имя пользователя и пароль не совпадают');
				redirect($this->uri->uri_string());
			else:
				$session_data = array('logon'=>md5($userinfo['login']),'userid'=>$userinfo['id']);
                $this->session->set_userdata($session_data);
                redirect("admin-panel/actions/control");
			endif;
		endif;
		
		if($this->loginstatus['status']):
			redirect('admin-panel/actions/control');
		endif;
		
		$this->load->view("users_interface/admin-login",$pagevar);
	}
	
	public function logoff(){
		
		$this->session->sess_destroy();
        redirect('');
	}
	
	public function password_restore(){
		
		$pagevar = array(
					'description'	=> '',
					'author'		=> '',
					'title'			=> '',
					'baseurl' 		=> base_url(),
					'loginstatus'	=> $this->loginstatus,
					'userinfo'		=> $this->user,
					'msgs'			=> $this->session->userdata('msgs'),
					'msgr'			=> $this->session->userdata('msgr'),
			);
		$this->session->unset_userdata('msgs');
		$this->session->unset_userdata('msgr');
		
		if($this->input->post('submit')):
			$_POST['submit'] == NULL;
			$this->form_validation->set_rules('email',' ','required|valid_email|trim');
			if(!$this->form_validation->run()):
				$this->session->set_userdata('msgr','Ошибка не верно заполнены необходимые поля.');
			else:
				$user = $this->usermodel->read_email_records($_POST['email']);
				if(!$user):
					$this->session->set_userdata('msgr','Указанный E-mail не найден.');
					redirect($this->uri->uri_string());
				endif;
				if(count($user)>1):
					$this->session->set_userdata('msgr','Ошибка. Обратитесь к администрации сайта');
					redirect($this->uri->uri_string());
				endif;
				$name = $user[0]['organization'];
				break;
				if(count($user)):
					$email = $_POST['email'];
					$login = $user[0]['login'];
					$password = $this->encrypt->decode($user[0]['cryptpassword']);
					ob_start();
					?>
					<p><strong>Здравствуйте,  <?=$name;?></strong></p>
					<p>Вами был произведен запрос на восстановления данных для аторизации:</p>
					<p><strong>Логин: <span style="font-size: 18px;"><?=$login;?></span> Пароль: <span style="font-size: 18px;"><?=$password;?></span></strong></p>
					<p>Желаем Вам удачи!</p> 
					<?
					$mailtext = ob_get_clean();
					
					$this->email->clear(TRUE);
					$config['smtp_host'] = 'localhost';
					$config['charset'] = 'utf-8';
					$config['wordwrap'] = TRUE;
					$config['mailtype'] = 'html';
					
					$this->email->initialize($config);
					$this->email->to($email);
					$this->email->from('admin@roscentrdpo.ru','АНО ДПО');
					$this->email->bcc('');
					$this->email->subject('Данные для доступа к личному кабинету');
					$this->email->message($mailtext);	
					$this->email->send();
				endif;
				$this->session->set_userdata('msgs','На адрес '.$email.' высланы логин и пароль.');
			endif;
			redirect($this->uri->uri_string());
		endif;
		
		$this->load->view("users_interface/password_restore",$pagevar);
	}
	
	public function randomPassword($length,$allow="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRSTUVWXYZ0123456789"){
	
		$i = 1;
		$ret = '';
		while($i<=$length):
			$max   = strlen($allow)-1;
			$num   = rand(0, $max);
			$temp  = substr($allow, $num, 1);
			$ret  .= $temp;
			$i++;
		endwhile;
		return $ret;
	}
}