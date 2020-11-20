<? defined('BASEPATH') or exit('No direct script access allowed');

class UtentesAdmin extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['login', 'serverConfig', 'adapter', 'util']);
        $this->load->library('pagination');
        $this->load->model('utenteModel');
        if (!isLoggedIn()) {
            redirect(base_url('noaccess'));
        }
    }

    /*
     * Index - Listagem de todos
     * os utentes.
     */
    public function index() {
        // Obter a página atual.
        $page = ($this->uri->segment(2)) ? $this->uri->segment(2) : 0;
        // Configuração da paginação.
        $config['base_url'] = base_url('utentesAdmin');
        $config['total_rows'] = $this->utenteModel->getCount();
        $config['per_page'] = PAGE_NUM_OF_ROWS; // helpers/ServerConfig_helper.php.
        $config['uri_segment'] = URI_SEGMENT; // helpers/ServerConfig_helper.php.
        // Inicializar a paginação.
        $this->pagination->initialize($config);
        $data['utentes'] = (new UtenteAdminAdapter)->adapt($this->utenteModel->getAllWithMoradaAndConsultas($config['per_page'], $page));
        $data['pagination'] = $this->pagination->create_links();
        // Carregar template.
        $this->renderer->render('admin/utentes', $data, true, true);
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
