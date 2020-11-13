<? defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Controlador base:
 * Homepage do site. Página 100%
 * pública que simplesmente promove o
 * site em si.
 */
class Base extends MY_Controller {
    public function __construct() {
        // Construtor-pai.
        parent::__construct();
    }

    public function index() {
        // Carregar template.
        $this->renderer->render('base');
	}
}