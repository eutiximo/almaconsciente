<?php

/*
 * Verificar si Wordpress no tiene instalado el plugin Timber y mandar una notificación en el "Admin" para instalarlo.
 */
if (!class_exists("Timber")) {
    add_action("admin_notices", function () {
        echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
    });
    
    add_filter('template_include', function( $template ) {
		return get_stylesheet_directory() . '/static/no-timber.html';
	});

	return;
}

//Fijar en "timber" las carpetas donde estan los templates o vistas
Timber::$dirname = array("views");

//Autoescape de valores en Twig
Timber::$autoescape = false;

/*
 * Clase para configuración y metodos de Timber
 */
class StarterSite extends Timber\Site {
    //Añadir soporte de Timber
    public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'theme_supports' ) );
        add_action("admin_head", array($this, "add_editor_styles"));
        add_action("admin_head", array($this, "hide_schema_color_admin"));
        add_action('login_enqueue_scripts', array($this, 'modify_login_style'));
        add_action("after_setup_theme", array($this, 'hide_adminbar'));
        add_action("user_register", array($this, "set_params_new_subscriber"));
        add_action("admin_enqueue_scripts", array($this, "custom_script_admin"));
		//add_action( 'init', array( $this, 'register_post_types' ) );
		//add_action( 'init', array( $this, 'register_taxonomies' ) );
        
        add_filter('timber_context', array( $this, 'add_to_context' ) );
        add_filter("login_redirect", array($this, "redirect_login_user"));
        add_filter("wp_logout", array($this, "redirect_logout_user"));
		//add_filter( 'get_twig', array( $this, 'add_to_twig' ) );
        
        add_action( 'pre_get_posts', array($this, 'before_set_query') ); 
        
		parent::__construct();
	}
    
    /* Metodo para añadir caracteristicas de soporte al tema */
    public function theme_supports() {
		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5', array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);

		/*
		 * Enable support for Post Formats.
		 *
		 * See: https://codex.wordpress.org/Post_Formats
		 */
		add_theme_support('post-formats', array(
            'aside',
            'image',
            'video',
            'quote',
            'link',
            'gallery',
            'audio',
        ));

		add_theme_support( 'menus' );
	}
    
    /* Añadir elementos por default al contexto de Timber */
    public function add_to_context( $context ) {
        global $wp;
		$context['site'] = $this;
		$context['menu'] = new Timber\Menu();
        $context['mainMenu'] = wp_get_nav_menu_items("main-menu");
        $context['linkNoticePrivacity'] = get_permalink( get_page_by_path("aviso-de-privacidad") );
        $context['linkWpLogin'] = wp_login_url();
        $context['linkWpRegistry'] = wp_registration_url();
        $context['i18n'] = include "parts/lenguages/i18n-esMX.php";
        $context['wp'] = $wp;
        
        $currentUser = new Timber\User();
        $currentUser->isLogged = $currentUser->ID != null ? true : false;
        $context['current_user'] = $currentUser;
        
        $context['paypal'] = array(
            "sandbox_uri" => "https://www.sandbox.paypal.com/cgi-bin/webscr",
            "serv_uri" => "https://www.paypal.com/cgi-bin/webscr",
            "service_uri" => "https://www.paypal.com/cgi-bin/webscr"
        );
        
		return $context;
	}
    
    /* Añadir una hoja de estilos a la parte de admin */
    public function add_editor_styles() {
        wp_enqueue_style("admin_styles", get_template_directory_uri(). "/styles/wp-admin-styles.css");
    }
    
    /* Modificar pantalla de login wordpress */
    public function modify_login_style() {
        echo '
            <style type="text/css">
                #login h1 a, .login h1 a {
                    width:320px;
                    height: 100px;
                    background: url('. get_stylesheet_directory_uri() .'/media/assets/logo-bold.svg) center center / contain no-repeat;
                }
                #wp-submit {
                    background-color: #77729B;
                    border:1px solid rgba(0,0,0, 0.3);
                    text-shadow:none;
                    box-shadow:rgba(0,0,0,0.5) 0 1px 0;
                }
            </style>
            <script>
                setTimeout(function () {
                    var LogoElem = document.querySelector("#login h1 a");
                    LogoElem.href = "'. home_url() .'";
                    LogoElem.removeAttribute("title");
                }, 200);
            </script>
        ';
    }
    
    /* Redireccionar al usuario al momento de logearse */
    public function redirect_login_user() {
        if (isset($_GET["redirect_to"])) {
            $url = urldecode($_GET["redirect_to"]);
            return $url;
            
        } elseif (isset($_POST["redirect_to"])) {
            $url = $_POST["redirect_to"] == site_url() ? '/' : urldecode($_POST["redirect_to"]);
            return site_url($url);
            
        } else {
            return site_url();
        }
    }
    
    /* Redireccionar a otra pagina al usuario al momento de cerrar sesion */
    public function redirect_logout_user() {
        wp_redirect(home_url());
        exit();
    }
    
    
    /* Ocultar la barra de administrador de wordpress en el sitio publico */
    public function hide_adminbar() {
        show_admin_bar(false);
    }
    
    /* Ocultar accion de cambiar color del thema de perfil excepto el administrador */
    public function hide_schema_color_admin() {
        $currentUser = wp_get_current_user();
        
        if ($currentUser->caps["subscriber"]) {
            remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
        }
    }
    
    /* Definir valores por defecto a los usuarios nuevos que se registren en la plataforma */
    public function set_params_new_subscriber($user_id) {
        $args = array(
            "ID" => $user_id,
            "admin_color" => "light"
        );
        wp_update_user($args);
    }
    
    /* Añadir codigo javascript al sitio de administrador */
    public function custom_script_admin() {
        $currentUser = wp_get_current_user();
        
        if ($currentUser->caps["subscriber"]):
        remove_menu_page("index.php");
        echo '
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var getWPlogo = document.querySelector("#wp-admin-bar-wp-logo"),
                    wpThanksFooter = document.querySelector("#footer-thankyou"),
                    wpBoxNotifications = document.querySelectorAll(".update-nag");
                    
                getWPlogo.style.display = "none";
                wpThanksFooter.style.display = "none";
                
                if (wpBoxNotifications.length) {
                    wpBoxNotifications.forEach(function (elem) { elem.remove(); });
                }
            });
        </script>
        ';
        endif;
    }
    
    /* Añadir regla para modificar query antes de que se ejecute. */
    public function before_set_query($query) {
        if ( $query->is_archive() ) {
            $query->set( 'post_type', 'post' );
            $query->set( 'posts_per_page', '6' );
       }
    }
    
    public function my_function() {
        return "public function";
    }
}

new StarterSite();


/* ------------------------------------------------------
    Clase con metodos para el funcionamiento de la pagina
   ------------------------------------------------------ */
class MainClass {
    public function __construct() {
        add_action( 'admin_post_nopriv_process_form', array($this, 'send_mail_data') );
        add_action( 'admin_post_process_form', array($this, 'send_mail_data') );
        add_action( 'admin_post_nopriv_wp_paypal_ipn', array($this, 'paypal_ipn_validate_pay') );
        add_action( 'register_form', array($this, 'form_register_frontend') );
        add_action( 'login_enqueue_scripts', array($this, 'add_html_head_wplogin'));
        
        add_filter( 'registration_errors', array($this, 'form_register_errors'), 10, 3 );
    }
    
    //Funcion para debugear datos y partes de codigo.
    public function debug($data = null) {
        if (isset($_GET["debug"])) {
            if ($data) print_r($data);
            if (!$data && $data !== false) print_r("No catch data");
            if ($data !== false) echo "\n----------------------------------------------------------------------------------\n";
            
            return true;
            
        } else {
            return false;
        }
    }
    
    // Funcion para renderear página.
    public function renderSite($tpls = false, $data = false) {
        $debugIsActive = $this->debug(false);
        
        if (!$debugIsActive && $tpls && $data) {
            Timber::render($tpls, $data);
        } elseif (!$tpls || !$data) {
            print_r("Error pass any paramenter -> render site, failed");
        }
    }
    
    /*
     *  Metodo para obtener la categoría activa del archivo o página singular.
        - Pasar como parametro el ID post para obtener su categoría o deja en NULO para obtener la categoría del query.
     */
    public function get_category_params($id = null) {
        
        $wpCategoryObject = !$id ? get_queried_object() : get_the_category($id)[0];
        $categoryParent = !$wpCategoryObject->parent ? $wpCategoryObject : get_category($wpCategoryObject->parent);
        
        $wpCategoryObject->data_parent = $categoryParent;
        
        return $wpCategoryObject;
    }
    
    /*
     *  Metodo para obtener los post destacados
        - Pasar como parametro un arreglo con los siguientes indices:
            > "parent" => integer   |   id de la categoría padre.
            > "cat_ID" => integer   |   id de la categoría.
     */
    public function get_great_posts($catparams = array()) {
        $catparams = !$catparams->parent ? $catparams : get_category($catparams->parent);
        
        return Timber::get_posts(array(
            "category" => $catparams->cat_ID,
            "orderby" => "rand",
            "numberposts" => 5,
            "meta_query" => array(
                "relation" => "AND",
                array(
                    "key" => "great_post",
                    "value" => 1
                )
            )
        ));
    }
    
    /*
     *  Metodo para obtener las subcategorías apartir de la categoría padre.
        - Pasar como parametro un arreglo con lo siguientes indicies:
            > "parent" => integer   |   id de la categoría padre.
            > "cat_ID" => integer   |   id de la categoría
     */
    public function get_subcategories($catparams = array()) {
        $catparams = !$catparams->parent ? $catparams : get_category($catparams->parent);
        return get_categories("child_of={$catparams->cat_ID}&hide_empty=0");
    }
    
    /*
     *  Metodo para obtener un arreglo de la fecha en concurrencia
     */
    public function xdate() {
        $day = date("d");
        $month = date("m");
        $year = date("Y");
        $hour = date("h:i:s");
        $nowdate = "{$day}-{$month}-{$year}";
        $fulldate = "{$day}-{$month}-{$year} {$hour}";
        $numDayWeek = date("N", strtotime($nowdate));
        $esp_days = array("lunes", "martes", "miercoles", "jueves", "viernes", "sabado", "domingo");
        $esp_month = array("enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre");
        $nameDayWeekEng = date("l", strtotime($nowdate));
        $nameDayWeekEsp = $esp_days[$numDayWeek - 1];
        $nameMonthEsp = $esp_month[$numDayWeek];
        return array(
            "day" => $day,
            "month" => $month,
            "year" => $year,
            "hour" => $hour,
            "nowdate" => $nowdate,
            "fulldate" => $fulldate,
            "numDayWeek" => $numDayWeek,
            "nameDayWeekEsp" => $nameDayWeekEsp,
            "nameDayWeekEng" => $nameDayWeekEng,
            "nameMonthEsp" => $nameMonthEsp,
            "arrMonthsEsp" => $esp_month,
            "arrDaysEsp" => $esp_days,
            "getTime" => intval($year.$month.$day)
        );
    }
    
    /*
     *  Metodo para obtener los talleres que se estan impartiendo segun la hora en la que el usuario entre a la plataforma.
        - 
     */
    public function get_current_workshops() {
        $workshops = Timber::get_posts(array( "category_name" => "talleres" ));
        $saveWorkshops = array();
        $date = $this->xdate();
        $useTest = false;
        
        if (!empty($workshops)):
        foreach ($workshops as $object) {
            $append_workshop = false;
            $schedule = get_field("workshop_schedule", $object->ID);
            
            if (!empty($schedule)):
            foreach($schedule as $elem) {
                $hour = $useTest ? "20:00:00" : $date["hour"];
                $nameDay = $useTest ? "lunes" : $date["nameDayWeekEsp"];
                $starWorkshop = $elem["start_workshop"];
                $endWorkshop = $elem["end_workshop"];
                $hasDay = array_search($nameDay, $elem["days"]);
                $hasTime = false;
                
                if (!$elem["is_loop"]) {
                    $perDays = array_map(function ($val) {
                        $splitDate = explode("-", $val["days"]);
                        $splitDate = $splitDate[2].$splitDate[1].$splitDate[0];
                        $splitDate = intval($splitDate);
                        return $splitDate;
                    }, $elem["days_workshop"]);
                    $getTime = $useTest ? 20181231 : $date["getTime"];
                    $hasTime = array_search($getTime, $perDays);
                }
                
                if (($hasDay !== false || $hasTime !== false) && strtotime($hour) >= strtotime($starWorkshop) && strtotime($hour) <= strtotime($endWorkshop)) {
                    $append_workshop = true;
                }
            }
            endif;
            
            if ($append_workshop):
            array_push($saveWorkshops, $object);
            endif;
        }
        endif;
        
        return $saveWorkshops;
    }
    
    /*
     *  Metodo que sirve para filtrar los dias o fechas de los talleres.
     */
    public function concatDays ($arrDays = array()) {
        $main_word;
        $countDays = count($arrDays) - 1;

        foreach($arrDays as $inx => $value) {
            $isInt = intval($value);
            $day = !$isInt ? ucwords($value) : $value;

            if ($countDays > 2 && !$isInt)
                $day = substr($day, 0, 2);

            $day .= $inx == $countDays ? '' : ($inx + 1 == $countDays ? ' y ' : ', ');
            $main_word .= "<span title='$value'>$day</span>";
        }

        return $main_word;
    }
    public function processWorkshopSchedule($data) { 
        $processedData = array();
        $nameMouths = self::xdate()["arrMonthsEsp"];
        
        foreach($data as $key => $value) {
            //Si el taller es semanal, procesar los dias de la semana.
            if ($value["is_loop"]):
            $processedData["days"] = "- ".self::concatDays($value["days"]);
            
            //Si el taller es definido por unos dias.
            else:
            $arrFachas = array();
            foreach($value["days_workshop"] as $key => $val) {
                $fecha = explode("-", $val["days"]);
                $dd = intval($fecha[0]);
                $mm = $nameMouths[$fecha[1] - 1];
                $yyyy = $fecha[2];
                
                if (!isset($arrFachas["$mm $yyyy"]))
                    $arrFachas["$mm $yyyy"] = array();
                
                array_push($arrFachas["$mm $yyyy"], $dd);
            }
            
            foreach($arrFachas as $key => $val) {
                $getDate = explode(' ', $key);
                $fecha = self::concatDays($val) . " de {$getDate[0]} ". substr($getDate[1], 2, 2);
                
                $processedData["days"] .= "<div class='d-block'>- $fecha</div>";
            }
            endif;
            
            $startWorkshop = explode(":", $value["start_workshop"]);
            $endWorkshop = explode(":", $value["end_workshop"]);
            
            $processedData["start_workshop"] = $startWorkshop[0].":".$startWorkshop[1];
            $processedData["end_workshop"] = $endWorkshop[0].":".$endWorkshop[1];
        }
        
        return $processedData;
    }
    
    /*
     *  Metodo para acciones del Formulario Contacto
        > El metodo *send_mail_data* es para contruir en formulario y enviarlo.
        > El metodo *sent_mail_status* es para saber si el correo se envio correctamente retornando un booleano.
     */
    public function send_mail_data () {
        $getIdContactPage = get_page_by_path("contacto")->ID;
        $current_page = $_POST["currentPage"];
        
        $name = sanitize_text_field($_POST["username"]);
        $email = sanitize_email($_POST["email"]);
        $message = sanitize_textarea_field($_POST["comment"]);
        
        $adminmail = get_field("admin_email", $getIdContactPage);
        $subject = "Almaconsciente.com.mx - Contacto";
        $headers = "Reply-to: ". $name ." <". $email .">";
        
        $recaptcha_success = self::reCaptcha();
        
        //Body message
        $msg = "Nombre: {$name}\nEmail: {$email}\n\nMensaje:\n{$message}\n";
        
        if ($recaptcha_success->success):
        $sendmail = wp_mail($adminmail, $subject, $msg, $headers);
        
        wp_redirect(home_url($current_page."/?sent={$sendmail}"));
        
        else:
            wp_redirect(home_url($current_page."/?captcha=0}"));
        endif;
        
    }
    public function sent_mail_status() {
        if ( isset($_GET['sent']) ){
            if ( $_GET['sent'] == '1'){
                return "success";
            }
            else {
                return "error";
            }
        } elseif (isset($_GET["captcha"])) {
            return "captcha-error";
        }
        else {
            return false;
        }
    }
    
    /*
     *  Metodo para validar pagos de PayPal mediante su sistema de IPN
        Documentación:
            - https://www.codigonexo.com/blog/programacion/php/tratar-los-datos-ipn-de-paypal/
            - https://www.youtube.com/watch?v=JzLQHNtgkK0
     */
    public function paypal_ipn_validate_pay() {
        $verification_success = require(get_template_directory()."/parts/payment-system/paypal-ipn-verification.php");
        
        if ($verification_success && isset($_POST["is_trustworthy"]) || isset($_POST['postman_test'])) {
            $custom = json_decode(stripslashes($_POST["custom"]));
            $dataRow = array(
                "id_product" => $custom->id_product,
                "name_product" => $custom->name_product,
                "payment_date" => $_POST["payment_date"],
                "quantity" => $_POST["quantity1"],
                "real_cost" => $_POST["mc_gross_1"],
                "tax" => $_POST["tax"],
                "total_payment" => $_POST["mc_gross"],
                "type_currency" => $_POST["mc_currency"],
                "name_user" => $_POST["first_name"]. " " . $_POST["last_name"],
                "status" => strtoupper($_POST["payment_status"]),
                "unlock" => strtoupper($_POST["payment_status"]) == "COMPLETED"
            );
            //Agregar producto al usuario
            add_row("payment_products", $dataRow, "user_{$custom->id_user}");
            //Enviar Email de comprobacion de compra al usuario.
            $paramsPost = $_POST;
            $paramsPost["custom"] = $custom;
            $this->send_mail_proof_of_payment($paramsPost);
        }
        
        //Escribir en un archivo que paso con el IPN de Paypal.
        
        if ($verification_success) {
            $textwrite = "-> Fuente confiable (Paypal.com)\n";
        } else {
            $textwrite = "-> La fuente no es confiable.\n";
        }
        foreach ($_POST as $key => $value) {
            $textwrite .= "{$key} => ${value}\n";
        }
        self::create_log_file("paypal", $textwrite);
    }
    public function send_mail_proof_of_payment($params) {
        //$emailUser = $params["payer_email"];
        $emailUser = $params["custom"]->wp_user_email;
        $subject = "Comprobante de compra";
        $headers = "No-reply - Almaconsciente.com.mx";
        
        //Body message
        $msg = "Hola {$params['first_name']} {$params['last_name']}. \n\nHaz hecho la compra del siguiente producto: \n*{$params['custom']->name_product} \nCosto neto: {$params['mc_gross_1']} {$params['mc_currency']} \nIVA: {$params['tax']} {$params['mc_currency']}\nCosto total: {$params['mc_gross']} {$params['mc_currency']} \n\nGracias por tu compra. \n\nNota: Si tu producto aún no se ha desbloqueado intenta refrescar la página, puede que PayPal aun este procesando tu petición.\nSi tu compra no se desbloquea en el lapso de una hora, ponte en contacto con el administrador de la página.";
        
        wp_mail($emailUser, $subject, $msg, $headers);
    }
    
    /*
     *  Metodos para añadir captcha al formulario de contacto y validarlo.
            > El metodo *form_register_frontend* sirve para añadir codigo a la vista del formulario como por ejemplo, otros inputs
            > El metodo *add_html_head_wplogin* srrve para añadir scripts en el head de la seccion "wp-login.php" y en este caso solo se añaden unos scripts para añadir el cdn de "recaptcha" y estilos para centrarlo y hacer presentable.
            > El metodo *form_register_errors* sirve para hacer push a mensajes de error por falta de validacion de campos en el formulario que se presentan al usuario.
                Tambien sirve para validar formulario, si no cumple las metricas el usuario no es registrado.
                
        Documentación:
            -https://codex.wordpress.org/Customizing_the_Registration_Form
            -https://codex.wordpress.org/Customizing_the_Login_Form
     */
    public function form_register_frontend() {
        echo "<div class='g-recaptcha d-inline-block' data-sitekey='6Ldf9YUUAAAAAMXXIClJajnZSReDn6fsUMppr09m'></div>";
        echo "";
    }
    public function add_html_head_wplogin() {
        if ($_GET["action"] == "register"){
            echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
            echo "
                <style type='text/css'>
                    .g-recaptcha {
                        position: relative;
                        min-height: 100px;
                    }
                    .g-recaptcha > * {
                        position:absolute;
                        top:50%;
                        left:50%;
                        transform: translate(-50%, -50%);
                    }
                </style>";
        }
    }
    public function form_register_errors($errors) {
        $recaptcha = self::reCaptcha();
        if (!$recaptcha->success)
            $errors->add('g-recaptcha-response', 'No se valido reCaptcha');
        
        return $errors;
    }
    
    /*
     * Metodo que funge como servicio para validar formularios con reCaptcha
     */
    public function reCaptcha () {
        $recaptcha = $_POST["g-recaptcha-response"];
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $data = array(
            "secret" => "6Ldf9YUUAAAAAO916V-2H-HBRFU2SlID5Ue_jAQJ",
            "response" => $recaptcha
        );
        $options = array(
            "http" => array(
                "method" => "POST",
                "content" => http_build_query($data)
            )
        );
        $contexto = stream_context_create($options);
        $verify = file_get_contents($url, false, $contexto);
        $recaptcha_success = json_decode($verify);
        self::create_log_file("recaptcha", $recaptcha);
        return $recaptcha_success;
    }
    
    /*
     * Metodo para crear archivos de log, que sirven para verificar datos.
     */
    public function create_log_file($file = null, $content = null) {
        $getDate = self::xdate();
        
        if ($file && $content):
        
        $fp = fopen(get_template_directory()."/logs/$file.log", "a+");
        fwrite($fp, "log of {$getDate['fulldate']} :\n".$content."\n\n-------------------------------------------------------------");
        fclose($fp);
        
        endif;
    }
}
$MC = new MainClass();

?>