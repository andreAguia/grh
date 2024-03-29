<?php

class ConcursoPublicacao {

    /**
     * Abriga as várias rotina referentes as publicações de concurso
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $idConcursoPublicacao integer null O id da publicação
     */
    private $idConcursoPublicacao = null;

##############################################################

    public function __construct($idConcursoPublicacao = null) {
        /**
         * Inicia a Classe somente
         * 
         * @param $idConcursoPublicacao integer null O id do concurso
         * 
         * @syntax $ConcursoPublicacao = new ConcursoPublicacao([$idConcursoPublicacao]);
         */
        $this->idConcursoPublicacao = $idConcursoPublicacao;
    }

##############################################################

    public function get_dados($idConcursoPublicacao = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $idConcursoPublicacao integer null O id do concurso
         * 
         * @syntax $ConcursoPublicacao->get_dados([$idConcursoPublicacao]);
         */
        # Joga o valor informado para a variável da classe
        if (!vazio($idConcursoPublicacao)) {
            $this->idConcursoPublicacao = $idConcursoPublicacao;
        }

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (vazio($this->idConcursoPublicacao)) {
            alert("É necessário informar o id do Publicação.");
            return;
        }

        # Pega os dados
        $select = 'SELECT * 
                     FROM tbconcursopublicacao
                    WHERE idConcursoPublicacao = ' . $this->idConcursoPublicacao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row;
    }

###########################################################

    public function exibePublicacao($idConcursoPublicacao) {
        /**
         * Exibe um link para a publicação
         * 
         * @param $idConcursoPublicacao integer null O id do Concurso
         * 
         * @syntax $ConcursoPublicacao->exibePublicacao($idConcursoPublicacao);
         */
        # Monta o arquivo
        $arquivo = PASTA_CONCURSO . $idConcursoPublicacao . ".pdf";

        # Verifica se ele existe
        if (file_exists($arquivo)) {

            # Monta o link
            $link = new Link(null, $arquivo, "Exibe a Publicação");
            $link->set_imagem(PASTA_FIGURAS . "doc.png", 20, 20);
            $link->set_target("_blank");
            $link->show();
        } else {
            echo "---";
        }
    }

###########################################################

    public function exibeDescricao($idConcursoPublicacao) {

        # Pega os dados
        $dados = $this->get_dados($idConcursoPublicacao);

        # Exibe o objeto
        echo $dados["descricao"];

        # Verifica se tem observação, se tiver exibe uma figura com mouseover
        if (!empty($dados["obs"])) {
            p($dados["obs"], "pObservacaoConcurso");
//            echo "&nbsp;&nbsp;&nbsp;&nbsp;";
//            toolTip("(Obs)", $dados["obs"]);
        }
    }

###########################################################

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        if (empty($id)) {
            echo "---";
        } else {

            # Pega array com os dias publicados
            $select = "SELECT obs
                     FROM tbconcursopublicacao
                    WHERE idConcursoPublicacao = {$id}";

            $retorno = $pessoal->select($select, false);
            if (empty($retorno[0])) {
                echo "---";
            } else {
                toolTip("Obs", $retorno[0]);
            }
        }
    }

###########################################################
}
