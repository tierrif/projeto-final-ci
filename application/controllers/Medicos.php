<? defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Controlador medicos:
 * Responsável pela listagem completa de
 * todos os médicos na aplicação.
 */
class Medicos extends MY_Controller {
    public function __construct() {
        // Construtor-pai.
        parent::__construct();
        $this->load->model('medicoModel');
        $this->load->library('pagination');
        $this->load->helper(['serverConfig', 'util', 'adapter']);
    }

    /*
     * Index - Listagem de todos
     * os médicos.
     */
    public function index() {
        // Obter a página atual.
        $page = ($this->uri->segment(2)) ? $this->uri->segment(2) : 0;
        // Configuração da paginação.
        $config['base_url'] = base_url('medicos');
        $config['total_rows'] = $this->medicoModel->getCount();
        $config['per_page'] = PAGE_NUM_OF_ROWS; // helpers/ServerConfig_helper.php.
        $config['uri_segment'] = URI_SEGMENT; // helpers/ServerConfig_helper.php.
        // Inicializar a paginação.
        $this->pagination->initialize($config);
        $data['medicos'] = (new PessoaSimplesAdapter)->adapt($this->medicoModel->getAllWithMorada($config['per_page'], $page));
        $data['pagination'] = $this->pagination->create_links();
        // Carregar template.
        $this->renderer->render('medicos', $data);
    }
}