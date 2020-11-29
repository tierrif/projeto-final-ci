<? defined('BASEPATH') or exit('No direct script access allowed');

class AuthModel extends MY_Model {
    public function checkPassword($usr, $pwd) {
        return $this->getByUsername($usr)['password'] === hash('sha256', $pwd);
    }

    public function getByUsername($usr) {
        return $this->db->get_where($this->getTable(), ['username' => $usr])->row_array();
    }

    public function isLoggedIn() {
        return $this->getBySession($this->session->userdata('token')) != null;
    }

    public function createSession($usr) {
        $this->db->where('username', $usr);
        $this->db->update($this->getTable(), ['session' => hash('sha256', time())]);
    }

    public function getBySession($sess) {
        return $this->db->get_where($this->getTable(), ['session' => $sess])->row_array();
    }

    public function hasPermission($permission) {
        $conta = $this->getBySession($this->session->userdata('token'));
        if (!$conta) return false;
        $perms = unserialize($conta['permissions']);
        return in_array($permission, $perms);
    }

    public function getTable() {
        return 'conta';
    }
}