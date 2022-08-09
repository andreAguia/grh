<?php

class PastaFuncional {

    /**
     * Abriga as várias rotina referentes a Controle de pasta funcional de um servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $idPasta integer null O id do documento da pasta do servidor
     */
    private $idPasta = null;

##############################################################

    public function __construct($idPasta = null) {
        /**
         * Inicia a Classe somente
         * 
         * @param $idPasta integer null O id do documento
         * 
         * @syntax $pastaFuncional = new PastaFuncional([$idPasta]);
         */
        $this->idPasta = $idPasta;
    }

##############################################################

    public function get_dados($idPasta = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $idPasta integer null O id do $idPasta
         * 
         * @syntax $pastaFuncional->get_dados([$idPasta]);
         */
        # Joga o valor informado para a variável da classe
        if (!vazio($idPasta)) {
            $this->idPasta = $idPasta;
        }

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (vazio($this->idPasta)) {
            alert("É necessário informar o id do Documento da Pasta Funcional.");
            return;
        }

        # Pega os dados
        $select = 'SELECT * 
                     FROM tbpasta
                    WHERE idPasta = ' . $this->idPasta;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row;
    }

###########################################################

    public function exibePasta($id) {
        /**
         * Exibe um link para a publicação
         * 
         * @param $idConcursoPublicacao integer null O id do Concurso
         * 
         * @syntax $ConcursoPublicacao->exibePublicacao($idConcursoPublicacao);
         */
        # Monta o arquivo
        $arquivo = PASTA_FUNCIONAL . $id . ".pdf";

        # Verifica se ele existe
        if (file_exists($arquivo)) {

            # Monta o link
            $link = new Link(null, $arquivo, "Exibe o Documento/Processo");
            $link->set_imagem(PASTA_FIGURAS . "doc.png", 20, 20);
            $link->set_target("_blank");
            $link->show();
        } else {
            echo "-";
        }
    }

##########################################################################################

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = "SELECT obs
                     FROM tbpasta
                    WHERE idPasta = {$id}";

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno[0]);
        }
    }

##########################################################################################
}
