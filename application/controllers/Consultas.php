<? defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Controlador consultas:
 * Responsável pela listagem completa de
 * todas as consultas na aplicação.
 */
class Consultas extends MY_Controller {
    public function __construct() {
        // Construtor-pai.
        parent::__construct();
        $this->load->model('consultaModel');
        $this->load->library('pagination');
        $this->load->helper(['serverConfig', 'util', 'adapter']);
    }

    /*
     * Index - Listagem de todas
     * os consultas.
     */
    public function index() {
        // Obter a página atual.
        $page = ($this->uri->segment(URI_SEGMENT)) ? $this->uri->segment(URI_SEGMENT) : 0;
        // Configuração da paginação.
        $config['base_url'] = base_url('Consultas/index');
        $config['total_rows'] = $this->consultaModel->getCount();
        $config['per_page'] = PAGE_NUM_OF_ROWS; // helpers/ServerConfig_helper.php.
        $config['uri_segment'] = URI_SEGMENT; // helpers/ServerConfig_helper.php.
        // Inicializar a paginação.
        $this->pagination->initialize($config);
        $data['consultas'] = (new ConsultaSimplesAdapter)->adapt($this->consultaModel->getConsultasOfTheDay($config['per_page'], $page));
        $data['pagination'] = $this->pagination->create_links();
        // Carregar template.
        $this->renderer->render('consultas', $data);
    }
}