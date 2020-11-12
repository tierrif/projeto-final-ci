<? defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Controlador utentes:
 * Responsável pela listagem completa de
 * todos os utentes na aplicação.
 */
class Utentes extends MY_Controller {
    public function __construct() {
        // Construtor-pai.
        parent::__construct();
    }

    /*
     * Index - Listagem de todos
     * os utentes.
     */
    public function index() {
        // TODO: Verificar permissões.
        // Carregar template.
        $this->renderer->render('utentes');
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