<? defined('BASEPATH') or exit('No direct script access allowed');

class ConsultasAdmin extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['serverConfig', 'adapter', 'util', 'form']);
        $this->load->library(['pagination', 'form_validation', 'upload']);
        $this->load->model(['consultaModel', 
            'enfermagemModel', 
            'produtoReceitaModel', 
            'produtoModel', 
            'receitaModel', 
            'enfermeiroModel',
            'utenteModel',
            'medicoModel'
        ]);
        if (!$this->authModel->isLoggedIn() || !$this->authModel->hasPermission('create-consultas')) {
            redirect(base_url('NoAccess'));
        }
    }

    /*
     * Index - Listagem de todas as
     * consultas.
     */
    public function index() {
        // Obter a página atual.
        $page = ($this->uri->segment(URI_SEGMENT)) ? $this->uri->segment(URI_SEGMENT) : 0;
        // Configuração da paginação.
        $config['base_url'] = base_url('ConsultasAdmin/index');
        $config['total_rows'] = $this->consultaModel->getCount();
        $config['per_page'] = PAGE_NUM_OF_ROWS; // helpers/ServerConfig_helper.php.
        $config['uri_segment'] = URI_SEGMENT; // helpers/ServerConfig_helper.php.
        // Inicializar a paginação.
        $this->pagination->initialize($config);
        $data['consultas'] = (new ConsultaAdminAdapter)->adapt($this->consultaModel->getAllWithReplacedKeys($config['per_page'], $page, 'ConsultasAdmin'));
        $data['pagination'] = $this->pagination->create_links();
        $consultaData = [
            'morada_form_include' => $this->renderer->manualRender('includes/morada_form', []),
            'action_uri' => base_url('ConsultasAdmin/details/-1/insert'),
            'utentes' => $this->utenteModel->getAll(),
            'medicos' => $this->medicoModel->getAll(),
            'medico_value' => set_value('medico'),
            'utente_value' => set_value('utente')
        ];
        // Iterar utentes e médicos para adicionar selected="selected".
        foreach ($consultaData['medicos'] as &$medico) {
            if ($consultaData['medico_value'] == $medico['id']) $medico['selected'] = 'selected="selected"';
        }
        foreach ($consultaData['utentes'] as &$utente) {
            if ($consultaData['utente_value'] == $utente['id']) $utente['selected'] = 'selected="selected"';
        }
        $data['consulta_form'] = $this->renderer->manualRender('details/consulta', $consultaData);

        // Carregar template.
        $this->renderer->render('admin/consultas', $data, true, true);
    }

    public function perUtente($id, $unfinishedOnly = null) {
        $data['consultas'] = (new ConsultaAdminAdapter)->adapt($this->consultaModel->getByUtente($id, $unfinishedOnly));
        $data['consulta_form'] = $this->renderer->manualRender('details/consulta', [
            'morada_form_include' => $this->renderer->manualRender('includes/morada_form', []),
            'action_uri' => base_url('ConsultasAdmin/details/-1/insert')
        ]);
        $data['utentes'] = $this->utenteModel->getAll();
        $data['medicos'] = $this->medicoModel->getAll();
        $data['medico_value'] = set_value('medico');
        $data['utente_value'] = set_value('utente');
        // Iterar utentes e médicos para adicionar selected="selected".
        foreach ($data['medicos'] as &$medico) {
            if ($data['medico_value'] == $medico['id']) $medico['selected'] = 'selected="selected"';
        }
        foreach ($data['utentes'] as &$utente) {
            if ($data['utente_value'] == $utente['id']) $utente['selected'] = 'selected="selected"';
        }

        // Carregar template.
        $this->renderer->render('admin/consultas', $data, true, true);
    }

    protected function onDelete($id) {
        // Elimina.
        $this->consultaModel->deleteAlongReceita($id);
    }

    /*
     * Details - Detalhes de uma
     * consulta específica.
     */
    protected function onDetailsRender($id, $fromForm) {
        // Dados dinâmicos a renderizar no mustache.
        $consulta = $this->consultaModel->getByIdWithReplacedKeys($id);
        // Obter todos os produtos e enfermeiros.
        $enfermeiros = $this->enfermeiroModel->getAll();
        $produtos = $this->produtoModel->getAll();
        $utentes = $this->utenteModel->getAll();
        $medicos = $this->medicoModel->getAll();
        // Se o pedido for feito pelo form, em POST, não setar $data da base de dados.
        if (!$fromForm) {
            $data = (new ConsultaDetailsAdapter)->adapt($consulta);
            $data['receita_form_include'] = $this->renderer->manualRender('includes/receita_form',
            (new ReceitaDetailsAdapter)->adapt($consulta['receita']));
            $data['enfermeiros_tosend'] = json_encode((new EnfermeiroJsonAdapter)->adapt($consulta['enfermeiros']));
            if (!$consulta['enfermeiros']) $data['enfermeiros_tosend'] = '[]';
            $data['produtos_tosend'] = json_encode((new ProdutoJsonAdapter)->adapt($consulta['receita']['produtos']));
            if (!$consulta['receita']['produtos']) $data['produtos_tosend'] = '[]';
        } else {
            if ($_FILES['pdf_file']['name'] && !$this->upload->do_upload('pdf_file')) {
                $data['alert'] = $this->renderer->manualRender('includes/form_alert', [
                    'alert_type' => 'alert-danger',
                    'alert_message' => $this->upload->display_errors('', '') // Sem delimitadores.
                ]);
                $this->setCancelled(); // Cancelar este evento.
                return $data;
            } else {
                // Setar a URI da receita.
                $data['pdf_file_uri'] = base_url('assets/receitas/' . $this->upload->data('file_name'));
            }
            $data = [
                'data_value' => set_value('data'),
                'estado_value' => set_value('estado') ? 'checked="checked"' : null,
                'medico_value' => set_value('medico'),
                'utente_value' => set_value('utente'),
                'receita_form_include' => $this->renderer->manualRender('includes/receita_form', [
                    'id_receita' => (set_value('idreceita') ? set_value('idreceita') : $consulta['receita']['id']),
                    'cuidado_value' => set_value('cuidado'),
                    'receita_value' => set_value('receita'),
                    'link-class' => (arrayValue($consulta['receita'], 'document') ? 'table-link' : 'disabled-link'),
                ]),
                'enfermeiros_tosend' => set_value('enfermeiros'),
                'produtos_tosend' => set_value('produtos')
            ];
        }

        // Prevenir erros no JS.
        if (!$data['produtos_tosend']) $data['produtos_tosend'] = '[]';
        if (!$data['enfermeiros_tosend']) $data['enfermeiros_tosend'] = '[]';

        // Dados em comum.
        $data['utentes'] = $utentes;
        $data['medicos'] = $medicos;
        $data['produtos_json'] = json_encode((new ProdutoJsonAdapter)->adapt($produtos));
        $data['enfermeiros_json'] = json_encode((new EnfermeiroJsonAdapter)->adapt($enfermeiros));
        $data['modals_include'] = $this->renderer->manualRender('includes/add_prod_enf_modal', []);
        // Iterar utentes e médicos para adicionar selected="selected".
        foreach ($data['medicos'] as &$medico) {
            if ($data['medico_value'] == $medico['id']) $medico['selected'] = 'selected="selected"';
        }
        foreach ($data['utentes'] as &$utente) {
            if ($data['utente_value'] == $utente['id']) $utente['selected'] = 'selected="selected"';
        }

        return $data;
    }

    protected function formElements() {
        // Inicializar regras por defeito.
        $toReturn = [
            [
                'field' => 'data',
                'label' => 'data',
                'rules' => 'required'
            ],
            [
                'field' => 'medico',
                'label' => 'médico',
                'rules' => 'required'
            ],
            [
                'field' => 'utente',
                'label' => 'utente',
                'rules' => 'required'
            ]
        ];

        // Verificar se foi passada receita ou cuidado.
        if ($this->input->post('receita') || $this->input->post('cuidado')) {
            // Se houver receita, estas são as regras.
            $toReturn[] = [
                [
                    'field' => 'receita',
                    'label' => 'receita',
                    'rules' => 'required'
                ],
                [
                    'field' => 'cuidado',
                    'label' => 'cuidado',
                    'rules' => 'required'
                ]
            ];
        }
        return $toReturn;
    }

    protected function handleDatabaseCalls($id) {
        // Arrays associativos que funcionam tanto para UPDATE como INSERT.
        $consulta = [
            'id' => ($id > 0 ? $id : null), // Ao inserir, se ID é nulo, auto-incrementa na BD.
            'data' => $this->input->post('data'),
            'estado' => $this->input->post('estado') ? 1 : 0,
            'idMedico' => $this->input->post('medico'),
            'idUtente' => $this->input->post('utente')
        ];
        $receita = [
            'id' => ($id > 0 ? $this->input->post('idreceita') : null),
            'cuidado' => $this->input->post('cuidado'),
            'receita' => $this->input->post('receita'),
            'document' => base_url('assets/receitas/' . $this->upload->data('file_name'))
        ];
        $enfermagens = [];
        // Se passado, enfermeiros está em JSON.
        if ($this->input->post('enfermeiros')) {
            // Passar de JSON para array associativo.
            $decoded = json_decode($this->input->post('enfermeiros'), true);
            if ($decoded) {
                // O JSON passado é um array. Iterá-lo.
                foreach ($decoded as $enfermeiro) {
                    $enfermagens[] = [
                        'idEnfermeiro' => $enfermeiro['id'],
                        'idConsulta' => $id
                    ];
                }
            }
        }
        $produtoreceitas = [];
        $produtos = [];
        // Se passado, produtos está em JSON.
        if ($this->input->post('produtos')) {
            // Passar de JSON para array associativo.
            $decoded = json_decode($this->input->post('produtos'), true);
            if ($decoded) {
                // O JSON passado é um array. Iterá-lo.
                foreach ($decoded as $produto) {
                    $produtoreceitas[] = [
                        'idProduto' => arrayValue($produto, 'id'),
                        'idReceita' => $this->input->post('idreceita')
                    ];
                    $produtos[] = [
                        'id' => arrayValue($produto, 'id'),
                        'titulo' => arrayValue($produto, 'titulo'),
                        'descricao' => arrayValue($produto, 'descricao')
                    ];
                }
            }
            
        }

        // Verificar se a consulta existe. Se sim, atualizar dados.
        if ($id > 0) {
            // Atualizar dados pelo model.
            $this->consultaModel->update($consulta);
            $this->receitaModel->update($receita);
            // Eliminar todas as enfermagens que existem para atualizar.
            $this->enfermagemModel->deleteEnfermagensByConsulta($id);
            // Iterar todas as novas enfermagens.
            foreach ($enfermagens as $enfermagem) {
                // Adicionar a nova enfermagem.
                $this->enfermagemModel->add($enfermagem);
            }
            // Índices de produtos e produtoreceitas são sincronizados.
            $i = 0;
            // Iterar todos os produtos.
            foreach ($produtos as $produto) {
                if (arrayValue($produto, 'id') != null && $produto['id'] > 0) {
                    // Atualizar se já existe.
                    $this->produtoModel->update($produto);
                    continue;
                }
                // Não existe, por isso, inserir.
                $id = $this->produtoModel->add($produto);
                $produtoreceitas[$i++]['idProduto'] = $id;
            }
            // Eliminar todos os produtosreceitas que existem para atualizar.
            $this->produtoReceitaModel->deleteProdutosReceitasByReceita($this->input->post('idreceita'));
            // Iterar todos os novos produtosreceitas.
            foreach ($produtoreceitas as $pr) {
                // Adicionar o novo produtoreceita.
                $this->produtoReceitaModel->add($pr);
            }
            // Já atualizámos, o código seguinte apenas serve para inserir.
            return $id;
        }

        // Inserir dados pelo model.
        $receitaId = $this->receitaModel->add($receita);
        $enfermeiro['idReceita'] = $receitaId;
        $newId = $this->consultaModel->add($enfermeiro);
        // Iterar todas as novas enfermagens.
        foreach ($enfermagens as $enfermagem) {
            // Adicionar a nova enfermagem.
            $this->enfermagemModel->add($enfermagem);
        }
        // Iterar todos os novos produtosreceitas.
        foreach ($produtoreceitas as $pr) {
            // Adicionar o novo produtoreceita.
            $this->produtoReceitaModel->add($pr);
        }
        // Iterar todos os produtos.
        foreach ($produtos as $produto) {
            if (arrayValue($produto, 'id')) {
                // Atualizar se já existe.
                $this->produtoModel->update($produto);
                continue;
            }
            // Não existe, por isso, inserir.
            $this->produtoModel->add($produto);
        }

        // Retornar o ID.
        return $newId;
    }

    protected function getTemplateName() {
        return 'consulta';
    }

    protected function temporaryData() {
        return [
            'data' => date('yy-m-d'),
            'cuidado' => 'Vazio ' . date('d/m/yy H:i:s')
        ];
    }
}
