<? defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Controlador que meramente serve para evitar repetição
 * de código. Deverá ser o controlador base de qualquer controlador
 * nesta aplicação.
 *
 * Ao herdar esta classe, para carregar uma template do
 * mustache simplesmente chamar:
 *
 *      $this->renderer->render(view)
 *
 * ou, com dados:
 *
 *      $this->renderer->render(view, data)
 *
 * ou, sem barra de navegação:
 *
 *      $this->renderer->render(view, data, false)
 *
 */
class MY_Controller extends CI_Controller {
    /*
     * No CodeIgniter, funções públicas são sempre
     * uma ação do controlador.
     * Por isso, não podemos fazer simplesmente uma função
     * nesta classe, mas sim um atributo público que assim
     * carregará as nossas próprias funções.
     * Por isso é que precisamos de usar algo como:
     *
     *      $this->load->view()
     *
     * em vez de:
     *
     *      $this->view()
     *
     * Para carregar views renderizadas com a biblioteca
     * Mustache, no entanto, deverá ser usada a nossa
     * classe CRenderer, que terá em si o método
     * render(), que carregará ao mesmo tempo o header,
     * a barra de navegação (opcional) e o footer.
     */
    public $renderer;

    public function __construct() {
        parent::__construct();
        $this->renderer = new Renderer($this->uri);
        $this->load->helper('permissions');
        // Carregar o modelo de pesquisa.
        $this->load->model('searchModel');
    }

    /*
     * NÃO OBRIGATÓRIO
     *
     * Qualquer pesquisa feita na página terá de ser feita nesta
     * ação. Herdar este método e colocar toda a lógica
     * correspondente ao controlador.
     */
    public function search() {
        // Por defeito, pesquisa utentes.
        // TODO: Implementar pesquisa.
        print_r($this->searchModel->searchByTableAndColumn('utente', 'nome', $this->input->get('search')));
    }
}

/*
 * Classe responsável pelo carregamento de ficheiros
 * mustache sem repetir código.
 * Uma instância desta classe deve ser guardada no controlador
 * abstrato CMustacheController.
 */
class Renderer {
    private $mustache;
    private $uri;

    public function __construct($uri) {
        // Carregar a pasta de templates para o mustache.
        $loader = new Mustache_Loader_FilesystemLoader('./templates');
        // Instanciar o mustache com o loader.
        $this->mustache = new Mustache_Engine(['loader' => $loader]);
        // Setar o URI.
        $this->uri = $uri;
    }

    /*
     * Renderiza a view já com o header e o
     * footer.
     *
     * $view - O caminho para a view (a partir de /templates)
     * $data - Array associativo com todos os dados passados na template.
     * $nav - Se true, mostrar barra de navegação, se false, não.
     */
    public function render($view, $data = [], $nav = true) {
        // Renderizar a template de início de página.
        echo $this->mustache->render('common/header', ['style_path' => base_url('assets/css/style.css')]);
        if ($nav) {
            // Todos os controladores da barra de navegação.
            $controllers = [
                '' => 'Home',
                'consultas' => 'Consultas',
                'utentes' => 'Lista de utentes',
                'produtos' => 'Fármacos'
            ];
            if (hasPermission('admin')) {
                $controllers['admin'] = 'Espaço admin';
            }
            // TODO: se tem login, botão é logout, senão é login.
            $loginButtonUri = '';
            $loginButtonText = '';
            // Definir o array associativo navData.
            $navData = [
                'home_url' => base_url(),
                'login_button_uri' => $loginButtonUri,
                'login_button_text' => $loginButtonText,
                'search_context' => base_url($this->uri->segment(1) . '/search')
            ];
            foreach ($controllers as $controller => $text) {
                $navData['nav_elements'][] = [
                    'active' => strtolower($this->uri->segment(1)) === $controller ? 'active' : null,
                    'uri' => base_url($controller),
                    'text' => $text
                ];
            }
            // Renderizar a barra de navegação com os dados adicionais.
            echo $this->mustache->render('common/nav', $navData);
        }
        // Renderizar a view dinâmica.
        echo $this->mustache->render($view, $data);
        // Renderizar a template de fim de página.
        echo $this->mustache->render('common/footer');
    }
}
