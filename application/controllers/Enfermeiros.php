<? defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Controlador enfermeiros:
 * Responsável pela listagem completa de
 * todos os enfermeiros na aplicação.
 */
class Enfermeiros extends MY_Controller {
    public function __construct() {
        // Construtor-pai.
        parent::__construct();
        $this->load->model('enfermeiroModel');
        $this->load->library('pagination');
        $this->load->helper(['serverConfig', 'util', 'adapter']);
    }

    /*
     * Index - Listagem de todos
     * os enfermeiros.
     */
    public function index() {
        // Obter a página atual.
        $page = ($this->uri->segment(2)) ? $this->uri->segment(2) : 0;
        // Configuração da paginação.
        $config['base_url'] = base_url('enfermeiros');
        $config['total_rows'] = $this->enfermeiroModel->getCount();
        $config['per_page'] = PAGE_NUM_OF_ROWS; // helpers/ServerConfig_helper.php.
        $config['uri_segment'] = URI_SEGMENT; // helpers/ServerConfig_helper.php.
        // Inicializar a paginação.
        $this->pagination->initialize($config);
        $data['enfermeiros'] = (new PessoaSimplesAdapter)->adapt($this->enfermeiroModel->getAllWithMorada($config['per_page'], $page));
        $data['pagination'] = $this->pagination->create_links();
        // Carregar template.
        $this->renderer->render('enfermeiros', $data);
    }
}