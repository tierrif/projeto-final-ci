<? defined('BASEPATH') or exit('No direct script access allowed');

class UtentesAdmin extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper('login');
        if (!isLoggedIn()) {
            redirect(base_url('noaccess'));
        }
    }

    /*
     * Details - Detalhes de um
     * utente específico.
     */
    public function details($id) {
        // Se o ID não for passado (/consultas/details, em vez de /consultas/details/:id), redireciona.
        if (!$id) {
            redirect(base_url('utentes'));
            return;
        }
    }
}
