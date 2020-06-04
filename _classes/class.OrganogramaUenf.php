<?php

class OrganogramaUenf {

    /**
     * Classe Exibe o organograma da UENF
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $diretoria string null A Diretoria do organograma. Se for null exibe o organograma de toda a Uenf
     * @var private $ignore    string null ALguma lotação a ser ignorada na exibição
     */
    private $diretoria = null;
    private $ignore = null;

###########################################################

    /**
     * Inicia o organograma
     */
    public function __construct($diretoria) {
        $this->diretoria = $diretoria;
    }

###########################################################

    public function set_ignore($ignore = null) {
        /**
         * Informa se a lotação será ignorada
         * 
         * @syntax $org->set_ignore($ignore);
         * 
         * @param $ignore string null O nome da lotação a ser ignorada
         */
        $this->ignore = $ignore;
    }

###########################################################

    public function show() {
        /**
         * Exibe o gráfico
         * 
         * @syntax $button->show();
         */
        # Cria o array a ser usado no organograma
        $organograma = array();

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Unidade Administrativa
        $uadmSelect = 'SELECT DISTINCT UADM FROM tblotacao WHERE ativo ORDER BY 1';
        $uadm = $pessoal->select($uadmSelect);

        if (is_null($this->diretoria)) {
            foreach ($uadm as $item) {
                $organograma[] = array($item[0], "", "");
            }
        }

        # Diretoria
        $dirSelect = 'SELECT DISTINCT DIR,UADM FROM tblotacao WHERE ativo ';

        if (is_null($this->diretoria)) {
            $dirSelect .= 'ORDER BY 1';
        } else {
            $dirSelect .= 'AND DIR="' . $this->diretoria . '" ORDER BY 1';
        }

        $dir = $pessoal->select($dirSelect);

        foreach ($dir as $item) {
            if (is_null($this->diretoria)) {
                $organograma[] = array($item[0], $item[1], "");
            } else {
                $organograma[] = array($item[0], "", "");
            }
        }

        # Lotações
        $lotacaoSelect = 'SELECT DIR, GER, nome FROM tblotacao WHERE ativo ';

        if (is_null($this->diretoria)) {
            $lotacaoSelect .= 'ORDER BY 1';
        } else {
            $lotacaoSelect .= 'AND DIR="' . $this->diretoria . '" ORDER BY 1,2';
        }

        $lotacao = $pessoal->select($lotacaoSelect);

        # Trata os dados para o organograma
        foreach ($lotacao as $item) {
            if ($this->ignore <> $item[1]) {
                $organograma[] = array($item[1], $item[0], $item[2]);
            }
        }

        $chart = new Organograma($organograma);
        $chart->show();
    }

    ###########################################################
}
