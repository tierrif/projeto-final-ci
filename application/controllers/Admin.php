<? defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Controlador admin:
 * Homepage do admin.
 */
class Admin extends MY_Controller {
    public function __construct() {
        // Construtor-pai.
        parent::__construct();
        if (!$this->authModel->isLoggedIn()) {
            redirect(base_url('NoAccess'));
        }
    }

    public function index() {
        // Carregar template.
        $this->renderer->render('admin', [], true, true);
	}
}