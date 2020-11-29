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
 * ou, com barra de navegação admin:
 * 
 *      $this->renderer->render(view, data, true, true)
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
    private $cancelled; // Cancelar a renderização da página de detalhes.

    public function __construct() {
        parent::__construct();
        $this->renderer = new Renderer($this->uri, $this->authModel);
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

    /*
     * Em páginas de back-office, os forms
     * recorrem a esta ação do controlador.
     * Se não for uma página em que haja um form
     * de back-office, NÃO IMPLEMENTAR.
     * 
     * Este método não deve ser implementado
     * diretamente, mas sim #onDetailsRender().
     */
    public function details($id = -1, $arg = null) {
        // Nome do controlador.
        $controller = get_class($this);

        if ($id < 0 && $arg !== 'insert') {
            show_404();
            return;
        }

        // Se inserir.
        if ($arg === 'insert') {
            /*
             * Para facilitar o uso do sistema,
             * não requerir regras na primeira inserção.
             * Basicamente, inserir. Dados temporários devem
             * ser retornados em temporaryData().
             */
            foreach ($this->temporaryData() as $key => $value) {
                if (!$this->input->post($key)) $_POST[$key] = $value;
            }
            
            $id = $this->handleDatabaseCalls(-1);
        }

        // Código irregular.
        $data = $this->onDetailsRender($id, $arg);

        // Componentes em comum.
        $data['action_uri'] = base_url($controller . '/details/' . $id . '/fromForm');
        $data['del_uri'] = base_url($controller . '/delete/' . $id);
        $data['form_footer_include'] = $this->renderer->manualRender('includes/form_footer', [
            'del_uri' => base_url($controller . '/delete/' . $id)
        ]);

        // O comportamento da ação é ligeiramente diferente ao responder ao form.
        if ($arg === 'fromForm' && !$this->cancelled) {
            // Verifica se #formElements() foi implementado.
            if (!$this->formElements()) {
                // Por defeito, é como que esta ação não existisse.
                show_404(); // Função global do CodeIgniter.
                return;
            }

            // Foi implementado, por isso, setar as regras.
            $this->form_validation->set_rules($this->formElements());
            // Eliminar tags <p> do validation_errors().
            $this->form_validation->set_error_delimiters('', '');

            // Executar o form validation.
            if ($this->form_validation->run()) {
                $data['alert'] = $this->renderer->manualRender('includes/form_alert', [
                    'alert_type' => 'alert-success',
                    'alert_message' => 'Sucesso.'
                ]);

                // Código irregular da base de dados.
                $this->handleDatabaseCalls($id);
            } else {
                $data['alert'] = $this->renderer->manualRender('includes/form_alert', [
                    'alert_type' => 'alert-danger',
                    'alert_message' => validation_errors()
                ]);
            }
        }

        // Renderiza.
        $this->renderer->render('details/' . $this->getTemplateName(), $data, true, true);
    }

    /*
     * Em páginas de back-office, os forms
     * recorrem a esta ação do controlador.
     * Se não for uma página em que haja um form
     * de back-office, NÃO IMPLEMENTAR.
     * 
     * Este método não deve ser implementado
     * diretamente, mas sim #onDetailsRender().
     */
    public function delete($id = 0) {
        // Se o ID não for passado (/delete, em vez de /delete/:id), redireciona.
        if (!$id) {
            redirect(base_url(get_class($this)));
            return;
        }

        // Código irregular.
        $this->onDelete($id);

        // Mostra um alert através de flashdata.
        $this->session->set_flashdata('alertType', 'alert-success');
        $this->session->set_flashdata('alertMessage', 'Eliminado com sucesso.');

        // Redireciona.
        redirect(base_url(get_class($this)));
    }

    /*
     * NÃO OBRIGATÓRIO
     * 
     * Retornar array associativo com toda a
     * informação dos elementos do form
     * desta página.
     */
    protected function formElements() {
        return false; // Retorna falso por defeito para lançar 404 se não for implementado em /update.
    }

    /*
     * NÃO OBRIGATÓRIO
     * 
     * Todas as chamadas à base de dados
     * numa resposta ao form devem ser
     * feitas neste método. 
     */
    protected function handleDatabaseCalls($id) {}

    /*
     * NÃO OBRIGATÓRIO
     * 
     * Chamado quando a ação /details é
     * chamada. 
     * 
     * Retornar os dados dinâmicos do mustache que
     * são irregulares.
     */
    protected function onDetailsRender($id, $arg) {
        show_404(); // Não renderiza nada, mas mostra 404 por defeito.
    }

    /*
     * NÃO OBRIGATÓRIO
     * 
     * Chamado quando a ação /delete é
     * chamada. 
     * 
     * Simplesmente fazer a ação necessária
     * para eliminar da base de dados.
     */
    protected function onDelete($id) {
        show_404(); // Não renderiza nada, mas mostra 404 por defeito.
    }

    /*
     * NÃO OBRIGATÓRIO
     * 
     * Retornar o nome da template de detalhes,
     * se aplicável. 
     */
    protected function getDetailsTemplate() {
        return null;
    }

    /*
     * NÃO OBRIGATÓRIO
     * 
     * Retornar os dados temporários de um
     * registo inserido por defeito, se
     * vazios. Chave do array associativo é
     * o nome do elemento do form e o valor
     * é o valor temporário.
     */
    protected function temporaryData() {
        return [];
    }

    /*
     * NÃO OBRIGATÓRIO
     * 
     * Retornar o nome da template de detalhes,
     * se aplicável. Não incluir /details. 
     */
    protected function getTemplateName() {
        return '';
    }

    protected function setCancelled() {
        $this->cancelled = true;
    }
}

/*
 * Classe responsável pelo carregamento de ficheiros
 * mustache sem repetir código.
 */
class Renderer {
    private $mustache;
    private $uri;
    private $authModel;

    public function __construct($uri, $authModel) {
        // Carregar a pasta de templates para o mustache.
        $loader = new Mustache_Loader_FilesystemLoader('./templates');
        // Instanciar o mustache com o loader.
        $this->mustache = new Mustache_Engine(['loader' => $loader]);
        // Setar o URI.
        $this->uri = $uri;
        // Setar a instância do authmodel.
        $this->authModel = $authModel;
    }

    /*
     * Renderiza a view já com o header e o
     * footer.
     *
     * $view - O caminho para a view (a partir de /templates)
     * $data - Array associativo com todos os dados passados na template.
     * $nav - Se true, mostrar barra de navegação, se false, não.
     */
    public function render($view, $data = [], $nav = true, $admin = false) {
        // Renderizar a template de início de página.
        echo $this->mustache->render('common/header', [
            'style_path' => base_url('assets/css/style.css'),
            'script_path' => base_url('assets/js/scripts.js')
        ]);
        if ($nav) {
            // Todos os controladores da barra de navegação.
            if ($admin) {
                $controllers = [
                    'Admin' => 'Home',
                    'ConsultasAdmin' => 'Consultas Admin',
                    'UtentesAdmin' => 'Utentes Admin',
                    'EnfermeirosAdmin' => 'Enfermeiros Admin',
                    'produtos' => 'Produtos'
                ];
            } else {
                $controllers = [
                    '' => 'Home',
                    'consultas' => 'Consultas',
                    'utentes' => 'Lista de utentes'
                ];
            }
            if ($this->authModel->hasPermission('admin') && !$admin) {
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
                'search_context' => base_url($this->uri->segment(1) . '/search'),
                'login_uri' => $this->authModel->isLoggedIn() ? base_url('Login/logout') : base_url('Login'),
                'login_text' => $this->authModel->isLoggedIn() ? 'Logout' : 'Login'
            ];
            foreach ($controllers as $controller => $text) {
                $navData['nav_elements'][] = [
                    'active' => strtolower($this->uri->segment(1)) === $controller ? 'active' : null,
                    'uri' => base_url($controller),
                    'text' => $text
                ];
            }
            $template = $admin ? 'common/nav_admin' : 'common/nav';
            // Renderizar a barra de navegação com os dados adicionais.
            echo $this->mustache->render($template, $navData);
        }
        // Renderizar a view dinâmica.
        echo $this->mustache->render($view, $data);
        // Renderizar a template de fim de página.
        echo $this->mustache->render('common/footer');
    }

    /*
     * Retorna em string o que for renderizado
     * à parte manualmente. Apenas deve ser usado
     * para includes.
     */
    public function manualRender($view, $data) {
        return $this->mustache->render($view, $data);
    }
}
