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
    private static $i18n;
    
    //Añadir soporte de Timber
    public function __construct() {
        self::$i18n = include "parts/lenguages/i18n-esMX.php";
        
		add_action( 'after_setup_theme', array( $this, 'theme_supports' ) );
        add_action("admin_head", array($this, "add_editor_styles"));
        add_action("admin_head", array($this, "hide_schema_color_admin"));
        add_action('login_enqueue_scripts', array($this, 'modify_login_style'));
        add_action("after_setup_theme", array($this, 'hide_adminbar'));
        add_action("user_register", array($this, "set_params_new_subscriber"));
        add_action("admin_enqueue_scripts", array($this, "custom_script_admin"));
        add_action("admin_menu", array($this, "remove_options_admin_menu"));
        add_action('edit_category_form_fields', array($this, 'cat_add_fields'));
        add_action('category_add_form_fields', array($this, 'cat_add_fields'));
        add_action('edited_category', array($this, 'save_extra_category_fields'));
        add_action('create_category', array($this, 'save_extra_category_fields'));
		//add_action( 'init', array( $this, 'register_post_types' ) );
		//add_action( 'init', array( $this, 'register_taxonomies' ) );
        
        add_filter('timber_context', array( $this, 'add_to_context' ) );
        add_filter("login_redirect", array($this, "redirect_login_user"));
        add_filter("wp_logout", array($this, "redirect_logout_user"));
        add_filter('wp_new_user_notification_email' , array($this, 'edit_user_notification_email'), 10, 3 );
		//add_filter( 'get_twig', array( $this, 'add_to_twig' ) );
        
        add_action( 'pre_get_posts', array($this, 'before_set_query') );
        
        parent::__construct();
	}
    
    public function edit_user_notification_email( $wp_new_user_notification_email, $user, $blogname ) {
        $key = get_password_reset_key($user);
        $getUser = $user->user_login;
        $getPass = $user->user_pass;
        $link_reset_password = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');
        
        $head_title = self::$i18n['mailtpl.user_registry.headTitle'];
        $body_title = sprintf(self::$i18n['mailtpl.user_registry.bodyTitle'], $blogname);
        
        $body_content = self::$i18n['mailtpl.user_registry.bodyContentPart1'];
        $body_content .= sprintf(self::$i18n['mailtpl.user_registry.bodyContentPart3'], $getUser);
        $body_content .= sprintf(self::$i18n['mailtpl.user_registry.bodyContentPart4'], $getPass);
        $body_content .= sprintf(self::$i18n['mailtpl.user_registry.bodyContentPart5'], $link_reset_password);
        $body_content .= self::$i18n['mailtpl.user_registry.bodyContentPart2'];
        $body_content .= self::$i18n['mailtpl.user_registry.bodyContentPart6'];
        
        $wp_new_user_notification_email['message'] = include("parts/mail-templates/user-registry.php");
        return $wp_new_user_notification_email;
    }
    
    /*  Esta function es temporal hasta que se paguen los derechos totales del sitio
        Documentation: https://codex.wordpress.org/Function_Reference/remove_menu_page
    */
    public function remove_options_admin_menu() {
        /*
            Temas: "themes.php"
            Personalizar: "customize.php?return=%2Fwp-admin%2Fthemes.php"
            Menus: "nav-menus.php"
            Editor: "theme-editor.php"
        */
        remove_menu_page( 'themes.php' );
        remove_menu_page( 'edit.php?post_type=acf-field-group' );
        remove_menu_page( 'options-general.php' );
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
        $context['i18n'] = self::$i18n;
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
                #wp-submit, #nav a:hover {
                    color: white !important;
                    background-color: #77729B;
                    border:1px solid rgba(0,0,0, 0.3);
                    text-shadow:none;
                    box-shadow:rgba(0,0,0,0.5) 0 1px 0;
                }
                #nav {
                    margin-top: 15px !important;
                    text-align: center;
                }
                #nav a {
                    display:inline-block;
                    padding: 5px 8px;
                    font-size: 14px;
                    border-radius: 3px;
                    background-color: white;
                    border:1px solid rgba(0,0,0,0.3);
                    box-shadow: rgba(0,0,0,0.2) 0 1px 5px;
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
    
    /* Add custom field to categories form edited
       Documentation: https://wpcrumbs.com/how-to-add-custom-fields-to-categories/
    */
    public function cat_add_fields($tag) {
        $t_id = $tag->term_id;
        $cat_meta = get_option("category_$t_id");
        
        if (current_filter() == 'edit_category_form_fields'):
        echo "
            <tr class='form-field'>
                <th scope='row' valing='top'>
                    <label for='Cat_meta[tplslug]'>
                    ". (self::$i18n['admin.cat.add_field.template.title']) ."
                    </label>
                </th>
                <td>
                    <input type='text' name='Cat_meta[tplslug]' id='Cat_meta[tplslug]' size='3' value='". ($cat_meta['tplslug'] ? $cat_meta['tplslug'] : '') ."'><br>
                    <span class='description'>". (self::$i18n['admin.cat.add_field.templete.description']) ."</span>
                </td>
            </tr>
        ";
        
        elseif (current_filter() == 'category_add_form_fields'):
        echo "
            <div class='form-field'>
                <label for='Cat_meta[tplslug]'>". (self::$i18n['admin.cat.add_field.template.title']) ."</label>
                <input type='text' name='Cat_meta[tplslug]' id='Cat_meta[tplslug]'>
                <p class='description'>". (self::$i18n['admin.cat.add_field.templete.description']) ."</p>
            </div>
        ";
        
        endif;
        
    }
    public function save_extra_category_fields($term_id) {
        if (isset( $_POST['Cat_meta'] )) {
            $t_id = $term_id;
            $cat_meta = get_option("category_$t_id");
            $cat_keys = array_keys($_POST['Cat_meta']);
            foreach ($cat_keys as $key) {
                if (isset($_POST['Cat_meta'][$key])){
                    $cat_meta[$key] = $_POST['Cat_meta'][$key];
                }
            }
        }
        
        update_option("category_$t_id", $cat_meta);
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
        add_action( 'user_register', array($this, 'custom_register_user'));
        add_action( 'show_user_profile', array($this, 'custom_user_profile_fields') );
        add_action( 'edit_user_profile', array($this, 'custom_user_profile_fields') );
        add_action( 'personal_options_update', array($this, 'save_custom_user_profile_fields') );
        add_action( 'edit_user_profile_update', array($this, 'save_custom_user_profile_fields') );
        
        add_filter( 'registration_errors', array($this, 'form_register_errors'), 10, 3 );
        add_filter( 'wp_mail_content_type', array($this, 'changeTypeContentMail'), 10, 1);
        add_filter( 'retrieve_password_message', array($this, 'custom_reset_password_mail'), 10, 2);
    }
    
    // Cambiar tipo de contenido para los mails.
    public function changeTypeContentMail() {
        return "text/html";
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
        $getTpl = get_option("category_{$wpCategoryObject->term_id}")['tplslug'];
        
        $wpCategoryObject->template = !$getTpl ? $wpCategoryObject->slug : $getTpl;
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
     *  Metodo para obtener de la Tienda los productos protegidos
        - Pasar como parametro las siguientes opciones.
            > "$id"         |   id del post
            > "$validate"   |   de tipo "boleano" que sirve para saver si paso la verificacion y obtener data
            > "$addParams"  |   un simple arreglo que sirve para añadir opciones al mismo arreglo que se va a retornar.
     */
    public function get_protected_products($id = null, $validate = false, $addParams = array()) {
        $arrData = array("aside" => array(), "top" => array(), "bottom" => array());
        if ($id !== null and $validate) {
            $getMediaAssets = get_field("media_assets", $id);
            
            foreach ($getMediaAssets as $value) {
                $pos = $value["position_content"];
                array_push($arrData[$pos], $value);
            }
            
            if (!empty($addParams))
                foreach ($addParams as $key => $value) { $arrData[$key] = $value; }
        }
        return $arrData;
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
     *  Metodo para obtener el o los destinatarios para envar el correo
     */
    public function get_contact_emails() {
        $getIdContactPage = get_page_by_path("contacto")->ID;
        $get_admin_recivers = get_field("admin_email", $getIdContactPage);
        $get_admin_recivers = explode(",", $get_admin_recivers);
        $get_admin_recivers = str_replace(" ", "", $get_admin_recivers);
        $get_admin_recivers = array_filter($get_admin_recivers, function ($value) { return filter_var($value, FILTER_VALIDATE_EMAIL); });
        
        return $get_admin_recivers;
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
        $current_page = $_POST["currentPage"];
        $adminmail = self::get_contact_emails();
        $recaptcha_success = self::reCaptcha();
        
        //Build body message
        $whichform = isset($_POST["whichform"]) ? sanitize_text_field($_POST["whichform"]) : "Contacto";
        
        if (isset($_POST['username'])) {
            $name = sanitize_text_field($_POST["username"]);
            $body_title = "$name te envio un mensaje";
            $body_content .= "<p><strong>Nombre: </strong> $name</p>";
        }
        if (isset($_POST['email'])) {
            $email = sanitize_email($_POST["email"]);
            $body_content .= "<p><strong>Email: </strong> $email</p>";
        }
        if (isset($_POST['phone'])) {
            $phone = sanitize_text_field($_POST["phone"]);
            $body_content .= "<p><strong>Teléfono: </strong> $phone</p>";
        }
        if (isset($_POST['comment'])) {
            $message = sanitize_textarea_field($_POST["comment"]);
            $body_content .= "<p><strong>Mensaje: </strong></p><p>$message</p>";
        }
        
        $subject = "Almaconsciente.com.mx - $whichform";
        $headers = "Reply-to: ". $name ." <". $email .">";
        $body = include("parts/mail-templates/contact-mail.php");
        
        if ($recaptcha_success->success) {
            $sendmail = wp_mail($adminmail, $subject, $body, $headers);
            wp_redirect(home_url($current_page."/?sent={$sendmail}"));
            
        } else {
            wp_redirect(home_url($current_page."/?captcha=0}"));
        }
        
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
    public function BKP_paypal_ipn_validate_pay() {
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
    public function paypal_ipn_validate_pay() {
        $custom = json_decode(stripslashes($_POST["custom"]));
        $paymentDate = isset($_POST['payment_date']) ? $_POST['payment_date'] : false;
        $quantity = isset($_POST['quantity1']) ? $_POST['quantity1'] : false;
        $mcGross1 = isset($_POST['mc_gross_1']) ? $_POST['mc_gross_1'] : false;
        $tax = isset($_POST['tax']) ? $_POST['tax'] : false;
        $mcGross = isset($_POST['mc_gross']) ? $_POST['mc_gross'] : false;
        $mcCurrency = isset($_POST['mc_currency']) ? $_POST['mc_currency'] : false;
        $firstName = isset($_POST['first_name']) ? $_POST['first_name'] : false;
        $lastName = isset($_POST['last_name']) ? $_POST['last_name'] : false;
        $status = isset($_POST['payment_status']) ? $_POST['payment_status'] : false;
        
        if ($paymentDate && $quantity && $mcGross1 && $tax && $mcGross && $mcCurrency && $firstName && $lastName && $status) {
            $dataRow = array(
                "id_product" => $custom->id_product,
                "name_product" => $custom->name_product,
                "payment_date" => $paymentDate,
                "quantity" => $quantity,
                "real_cost" => $mcGross1,
                "tax" => $tax,
                "total_payment" => $mcGross,
                "type_currency" => $mcCurrency,
                "name_user" => $firstName. " " . $lastName,
                "status" => strtoupper($status),
                "unlock" => strtoupper($status) == "COMPLETED"
            );
            //Agregar producto al usuario
            add_row("payment_products", $dataRow, "user_{$custom->id_user}");
            //Enviar Email de comprobacion de compra al usuario.
            $paramsPost = $_POST;
            $paramsPost["custom"] = $custom;
            $this->send_mail_proof_of_payment($paramsPost);
        }
        
        $verification_success = require(get_template_directory()."/parts/payment-system/paypal-ipn-verification.php");
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
        if (isset($_POST['testSendMailPaypal'])) {
            $custom = array(
                "wp_user_email" => $_POST['wp_user_email'],
                "name_product" => $_POST['name_product']
            );
            $params = array(
                "custom" => (object) $custom,
                "payer_email" => $_POST['payer_email'],
                "first_name" => $_POST['first_name'],
                "last_name" => $_POST['last_name'],
                "mc_currency" => $_POST['mc_currency'],
                "mc_gross_1" => $_POST['mc_gross_1'],
                "mc_gross" => $_POST['mc_gross'],
                "tax" => $_POST['tax']
            );
        }
        $adminEmail = $this->get_contact_emails()[0];
        $emailUser = array($params["custom"]->wp_user_email, $params["payer_email"]);
        $subject = "Comprobante de compra";
        $headers = "No-reply - Almaconsciente.com.mx";
        
        //Body message
        $head_title = $subject;
        $body_title = "Gracias por tu compra!";
        $body_content = sprintf("<p>Hola <strong>%s</strong> </p>", ($params['first_name'].' '.$params['last_name']) );
        $body_content .= sprintf("<p>Haz hecho la compra del siguiente producto <strong>%s</strong></p>", $params['custom']->name_product);
        $body_content .= sprintf("<p><strong>Costo neto:</strong> %s</p>", ($params['mc_gross_1'].' '.$params['mc_currency']) );
        $body_content .= sprintf("<p><strong>IVA:</strong> %s</p>", ($params['tax'].' '.$params['mc_currency']) );
        $body_content .= sprintf("<p><strong>Costo total:</strong> %s</p>", ($params['mc_gross'].' '.$params['mc_currency']) );
        $body_content .= "<p><strong>Nota:</strong> En caso de que hayas comprado un producto en la tienda, si este no se desbloquea intenta refrescar la página, es posible que aún se esté procesando tu compra.</p>";
        $body_content .= sprintf("<p>Si tu producto no se ha desbloqueado en el lapso de los próximos 15 minutos, contáctanos a este correo <strong>%s</strong> para brindarte soporte.</p>", $adminEmail);
        
        $body = include("parts/mail-templates/user-registry.php");
        
        wp_mail($emailUser, $subject, $body, $headers);
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
        $first_name = !empty( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';
        $last_name = !empty( $_POST['last_name'] ) ? sanitize_text_field( $_POST['last_name'] ) : '';
        $phone = !empty( $_POST['user_phone'] ) ? sanitize_text_field( $_POST['user_phone'] ) : '';
        $address = !empty( $_POST['user_address'] ) ? sanitize_text_field( $_POST['user_address'] ) : '';
        
        $custom = "
            <p style='display:flex;flex-wrap:norap;'>
                <label for='first_name'>Nombre<br />
                <input type='text' name='first_name' id='first_name' class='input' value='$first_name' size='25' /></label>
                &nbsp;&nbsp;&nbsp;
                <label for='last_name'>Apellidos<br />
                <input type='text' name='last_name' id='last_name' class='input' value='$last_name' size='25' /></label>
            </p>
            <p><label for='user_phone'>Teléfono<br />
                <input type='text' name='user_phone' id='user_phone' class='input' value='$phone' size='25' /></label>
            </p>
            <p><label for='user_address'>Dirección y cuidad<br />
                <input type='text' name='user_address' id='user_address' class='input' value='$address' size='25' /></label>
            </p>
            <div class='g-recaptcha d-inline-block' data-sitekey='6Ldf9YUUAAAAAMXXIClJajnZSReDn6fsUMppr09m'></div>
        ";
        echo $custom;
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
        
        if (empty( $_POST['first_name'] ) || ! empty( $_POST['first_name'] ) && trim( $_POST['first_name'] ) == '') {
            $errors->add('first_name_error', sprintf('<strong>%s</strong>: %s',__( 'ERROR', 'mydomain' ),__( 'Debes incluir tu nombre', 'mydomain' ) ));
        }
        elseif (empty( $_POST['last_name'] ) || ! empty( $_POST['last_name'] ) && trim( $_POST['last_name'] ) == '') {
            $errors->add('last_name_error', sprintf('<strong>%s</strong>: %s',__( 'ERROR', 'mydomain' ),__( 'Debes incluir tu apellido', 'mydomain' ) ));
        }
        elseif (empty( $_POST['user_phone'] ) || ! empty( $_POST['user_phone'] ) && trim( $_POST['user_phone'] ) == '') {
            $errors->add('user_phone_error', sprintf('<strong>%s</strong>: %s',__( 'ERROR', 'mydomain' ),__( 'Debes incluir tu teléfono', 'mydomain' ) ));
        }
        elseif (empty( $_POST['user_address'] ) || ! empty( $_POST['user_address'] ) && trim( $_POST['user_address'] ) == '') {
            $errors->add('user_address_error', sprintf('<strong>%s</strong>: %s',__( 'ERROR', 'mydomain' ),__( 'Debes incluir tu dirección y cuidad', 'mydomain' ) ));
        }
        else if (!$recaptcha->success) {
            $errors->add('user_address_error', sprintf('<strong>%s</strong>: %s',__( 'ERROR', 'mydomain' ),__( 'No se valido reCaptcha', 'mydomain' ) ));
        }
        
        
        return $errors;
    }
    public function custom_register_user($user_id) {
        if ( !empty($_POST['first_name']) )
            update_user_meta( $user_id, 'first_name', sanitize_text_field( $_POST['first_name'] ) );
        
        if ( !empty($_POST['last_name']))
            update_user_meta( $user_id, 'last_name', sanitize_text_field( $_POST['last_name'] ) );
        
        if ( !empty($_POST['user_phone']))
            update_user_meta( $user_id, 'user_phone', sanitize_text_field( $_POST['user_phone'] ) );
        
        if ( !empty($_POST['user_address']))
            update_user_meta( $user_id, 'user_address', sanitize_text_field( $_POST['user_address'] ) );
    }
    
    /*
     *  Agregar campos personalizados a la vista de editar usuarios y guardar estado si son editados
     */
    public function custom_user_profile_fields ($user) {
        $getMetadataUser = get_user_meta($user->ID);
        $userPhone = $getMetadataUser['user_phone'][0];
        $userAddress = $getMetadataUser['user_address'][0];
        
        $custom = "
            <table class='form-table' id='custom_data_user'>
                <tr>
                    <th><label for='user_phone'><b>Teléfono</b></label></th>
                    <td><input type='text' name='user_phone' id='user_phone' value='$userPhone' class='regular-text ltr' required><br>
                        <span class='description'></span></td>
                </tr>
                <tr>
                    <th><label for='user_address'><b>Dirección y cuidad</b></label></th>
                    <td><input type='text' name='user_address' id='user_address' value='$userAddress' class='regular-text ltr' required><br>
                        <span class='description'></span></td>
                </tr>
            </table>
            
            <script>
                var getFormTable = document.querySelectorAll('.form-table'),
                    getCustom = document.querySelector('#custom_data_user'),
                    cloneCustom = getCustom.cloneNode(true).querySelector('tbody').childNodes;
                    
                    getCustom.remove();
                    
                    getFormTable = getFormTable[2].querySelector('tbody');
                    
                    cloneCustom.forEach(function(v) { getFormTable.appendChild(v); });
                
            </script>
        ";
        
        
        echo $custom;
    }
    public function save_custom_user_profile_fields ($user_id) {
        if (!current_user_can( 'edit_user', $user_id )) {
            return false;
        }
        
        update_user_meta($user_id, 'user_phone', $_POST['user_phone']);
        update_user_meta($user_id, 'user_address', $_POST['user_address']);
    }
    
    /*
     *  Modificar plantilla de recuperacion de contraseña
     */
    public function custom_reset_password_mail($message, $key) {
        $user_data = '';
        // If no value is posted, return false
        if( ! isset( $_POST['user_login'] ) ){
                return '';
        }
        // Fetch user information from user_login
        if ( strpos( $_POST['user_login'], '@' ) ) {
            $user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
        } else {
            $login = trim($_POST['user_login']);
            $user_data = get_user_by('login', $login);
        }
        if( ! $user_data  ){
            return '';
        }
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;
        $link_reset_password = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
        
        $body_head = "Haz solicitado un cambio de contraseña";
        $body_content = "<p>A continuación hay un enlace en donde podrás reestablecer tu contraseña:</p>";
        $body_content .= "<center><a href='$link_reset_password'>$link_reset_password</a></center>";
        $body_content .= "<p></p>";
        $body = include("parts/mail-templates/user-registry.php");
        return $body;
    }
    
    /*
     *  Correo que se envia al cliente si su compra fue exitosa.
     */
    public function send_welcome_mail() {
        $currentUser = wp_get_current_user();
        
        $body_title = "Hola, gracias por ser parte de esta revolución de amor.";
        $body_content = "
        <p>He soñado con un mundo donde tanto hombres como mujeres se cuiden, se amen, se valoren, vean la magia y la maravilla que son y este reto es parte de ese sueño, espero que después de 11 días de tu total dedicación puedas crear relaciones expansivas, no quiero que nunca vuelvas aguantar lo inaguantable \"por amor\", ni que dudes de ti, de tu poder, de tu grandeza, quiero saberte libre y feliz, amando y honrando tu cuerpo,mi corazón está lleno de agradecimiento por que estés aquí, que me permites ser parte de este movimiento, pero principalmente gracias por querer amarte más, mucho más.</p>

        <p><strong>¿Qué sigue?</strong></p>
        <p>El 14 de febrero ( para realmente festejar el amor del bueno) <strong>recibirás un mail con toda la información del programa, manual de trabajo, link con accesos a los audios, manual de alimentación, recetario y más</strong></p>

        <p><strong>El reto lo empezamos juntos (20 de febrero) y lo terminamos juntos (2 de marzo)</strong> por lo que te agradezco agendes con anticipación 30 minutos diarios para escuchar los audios con total atención así como los ejercicios, dependerá del día pero máximo serán 30 minutos diarios más la preparación de tu alimentación.</p>
        
        <p><strong>Además te invito a unirte a grupo de Facebook</strong> donde podremos aclarar dudas, compartir nuestra experiencia y avances del programa.</p>

        <p><a href='https://m.facebook.com/groups/548556238997160'>https://m.facebook.com/groups/548556238997160</a></p>

        <p>Espero que sea una experiencia maravillosa <strong>¡Una vez más gracias!</strong> Pronto recibirás más noticias por mail y en el grupo de Facebook.<p>

        <p>Con amor,<br>Alma</p>
        ";
        $subject = "Almaconsciente.com.mx - Gracias";
        $headers = "No-reply - Almaconsciente.com.mx";
        $body = include("parts/mail-templates/user-registry.php");
        $email = $currentUser->data->user_email;
        
        wp_mail($email, $subject, $body, $headers);
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