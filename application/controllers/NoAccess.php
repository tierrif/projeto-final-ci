<? defined('BASEPATH') or exit('No direct script access allowed');

class NoAccess extends MY_Controller {
    public function index() {
        $this->renderer->render('noaccess');
    }
}