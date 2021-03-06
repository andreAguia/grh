<?php

class Alertas {

    /**
     * Classe Alertas encapsula as rotinas dos alertas
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    private $itens = array(
        array('Licenças', 'licencas'),
        array('Triênio', 'trienio'),
        array('Auxílio Creche', 'creche'),
        array('Motorista', 'motorista'),
        array('Aposentadoria', 'aposentadoria'),
        array('Cadastro Geral', 'cadastro'),
        array('Perfil', 'perfil'),
        array('Concurso', 'concurso'),
        array('Cargo em Comissão', 'comissao'),
        array('Cedidos', 'cedidos'),
        array('Férias', 'ferias'),
        array('TRE', 'tre'),
        array('Progressão', 'progressao'),
        array('Benefícios', 'beneficios')
    );

    ######################################################################################################################    

    public function menu($fase) {

        # Ordena as categorias

        function cmp($a, $b) {
            # Função específica que compara se $a é maior que $b
            return $a[0] > $b[0];
        }

        // Ordena
        usort($this->itens, 'cmp');

        $menu = new Menu();
        $menu->add_item('titulo', 'Categorias');

        foreach ($this->itens as $ii) {
            if ($fase == $ii[1]) {
                $menu->add_item('link', '<b>| ' . $ii[0] . ' |</b>', '?fase=menu&categoria=' . $ii[1]);
            } else {
                $menu->add_item('link', $ii[0], '?fase=menu&categoria=' . $ii[1]);
            }
        }

        #$menu->add_item('link','Temporal','?fase=temporalCargo');  # Retirado por imprecisão
        $menu->show();
    }

    ######################################################################################################################

    public function getNomeCategoria($categoria) {

        # Verifica se a categoria foi preenchida
        if (empty($categoria)) {
            return null;
        }

        foreach ($this->itens as $ii) {
            if ($categoria == $ii[1]) {
                return $ii[0];
            }
        }
    }

}
