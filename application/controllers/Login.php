<? defined('BASEPATH') or exit('No direct script access allowed');

class Login extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('login');
    }

    public function index() {
        $this->renderer->render('login');
    }
}