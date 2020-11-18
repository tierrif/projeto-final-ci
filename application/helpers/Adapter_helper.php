<? defined('BASEPATH') or exit('No direct script access allowed');

abstract class Adapter {
    /*
     * Retornar um array associativo que contém
     * como chave o novo nome, e o valor com o
     * nome original. Por exemplo:
     * Array original:
     * [
     *   'nome' => 'Tierri Ferreira',
     *   'morada' => [
     *      'linha1' => 'Rua abcde',
     *      'cidade' => 'Funchal'
     *   ]
     * ]
     * A retornar:
     * [
     *   'nome', // Pode ser equivalente a 'nome' => 'nome'
     *   'cidade' => 'morada/cidade' // cidade, conteúdo do array da morada.
     * ]
     */
    public abstract function toAdapt();

    /*
     * Adaptar. Retornará em array no formato
     * do adaptador respetivo, delimitado
     * pelo método abstrato toAdapt().
     */
    public function adapt($originalContent) {
        $toReturn = [];
        // Primeiro iterar o array original.
        foreach ($originalContent as $item) {
            // Inicializar array adaptado.
            $adaptedArray = [];
            // Iterar.
            foreach ($this->toAdapt() as $key => $value) {
                if (is_numeric($key)) $key = $value; // No caso de ser elemento simples.
                // Transformar a string, fazendo com que possamos iterar o que está ao lado de '/'.
                $split = explode('/', $value);
                // A primeira posição. Será atualizada em cada nível.
                $pos = arrayValue($item, $split[0]);
                // Variável de controlo para prevenir repetição do primeiro elemento.
                $i = 0;
                foreach ($split as $element) {
                    // Se não existirem barras, isto não é necessário.
                    if (count($split) === 1) break;
                    // Prevenir repetição do primeiro elemento.
                    if ($i++ === 0) continue;
                    // Atualizar o array com novo nível.
                    $pos = $pos[$element];
                }
                $adaptedArray[$key] = $pos;
            }
            $toReturn[] = $adaptedArray;
        }
        // Retornar o array adaptado.
        return $toReturn;
    }
}

class UtenteSimplesAdapter extends Adapter {
    public function toAdapt() {
        return [
            'nome',
            'cidade' => 'morada/city'
        ];
    }
}

class UtenteAdminAdapter extends Adapter {
    public function toAdapt() {
        return [
            'nome',
            'numero_utente' => 'nUtente',
            'cidade' => 'morada/city',
            'consultas_inacabadas',
            'consultas_uri'
        ];
    }
}