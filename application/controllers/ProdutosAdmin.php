<? defined('BASEPATH') or exit('No direct script access allowed');

class ProdutosAdmin extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['serverConfig', 'adapter', 'util', 'form']);
        $this->load->model('produtoModel');
        $this->load->library(['pagination', 'form_validation']);
        if (!$this->authModel->isLoggedIn() || !$this->authModel->hasPermission('manage-products')) {
            redirect(base_url('NoAccess'));
        }
    }

    /*
     * Index - Listagem de todos
     * os produtos.
     */
    public function index() {
        // Obter a página atual.
        $page = ($this->uri->segment(URI_SEGMENT)) ? $this->uri->segment(URI_SEGMENT) : 0;
        // Configuração da paginação.
        $config['base_url'] = base_url('ProdutosAdmin/index');
        $config['total_rows'] = $this->produtoModel->getCount();
        $config['per_page'] = PAGE_NUM_OF_ROWS; // helpers/ServerConfig_helper.php.
        $config['uri_segment'] = URI_SEGMENT; // helpers/ServerConfig_helper.php.
        // Inicializar a paginação.
        $this->pagination->initialize($config);
        $data['produtos'] = (new ProdutoAdminAdapter)->adapt($this->produtoModel->getAllWithDetails($config['per_page'], $page));
        $data['pagination'] = $this->pagination->create_links();
        $data['produto_form'] = $this->renderer->manualRender('details/produto', [
            'action_uri' => base_url('ProdutosAdmin/details/-1/insert')
        ]);

        // Carregar template.
        $this->renderer->render('admin/produtos', $data, true, true);
    }

    protected function onDelete($id) {
        // Elimina.
        $this->produtoModel->delete($id);
    }

    /*
     * Details - Detalhes de um
     * produto específico.
     */
    protected function onDetailsRender($id, $fromForm) {
        // Dados dinâmicos a renderizar no mustache.
        $produto = $this->produtoModel->getById($id);
        // Se o pedido for feito pelo form, em POST, não setar $data da base de dados.
        if (!$fromForm) {
          $data = (new ProdutoDetailsAdapter)->adapt($produto);
        } else $data = ['titulo_value' => set_value('titulo'), 'descricao_value' => set_value('descricao')];
          
        $data['bar'] = $this->renderer->manualRender('includes/bar', ['base_controller' => base_url(get_class($this))]);

        return $data;
    }

    protected function formElements() {
        return [
            [
                'field' => 'titulo',
                'label' => 'título',
                'rules' => 'required'
            ]
        ];
    }

    protected function handleDatabaseCalls($id) {
        // Arrays associativos que funcionam tanto para UPDATE como INSERT.
        $produto = [
            'id' => ($id > 0 ? $id : null), // Ao inserir, se ID é nulo, auto-incrementa na BD.
            'titulo' => $this->input->post('titulo'),
            'descricao' => $this->input->post('descricao')
        ];

        // Verificar se o produto existe. Se sim, atualizar dados.
        if ($id > 0) {
            // Atualizar dados pelo model.
            $this->produtoModel->update($produto);
            // Já atualizámos, o código seguinte apenas serve para inserir.
            return $id;
        }

        $newId = $this->produtoModel->add($produto);

        // Retornar o ID.
        return $newId;
    }

    protected function getTemplateName() {
        return 'produto';
    }

    protected function temporaryData() {
        return [
            'titulo' => 'Vazio ' . date('d/m/yy H:i:s')
        ];
    }
}
