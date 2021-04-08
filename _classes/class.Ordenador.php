<?php

class Ordenador {
    /**
     * Abriga as várias rotina do cadastro de designação para Ordenador de despesas
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    /**
     * Método Construtor
     */
    public function __construct() {
        
    }

    ###########################################################

    function get_dados($idOrdenador) {

        /**
         * fornece a próxima tarefa a ser realizada
         */
        # Pega os dados
        $select = "SELECT *
                   FROM tbordenador
                  WHERE idOrdenador = {$idOrdenador}";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados;
    }

    ###########################################################

    function exibeDadosDesignacao($idOrdenador) {

        /**
         * Exibe todos os dados de uma designação
         */
        # Pega os dados
        $select = "SELECT dtDesignacao,
                          dtPublicDesignacao,
                          pgPublicDesignacao,
                          numProcDesignacao,
                          dtAtoDesignacao
                   FROM tbordenador
                  WHERE idOrdenador = {$idOrdenador}";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        if (!empty($dados["dtDesignacao"])) {
            p("Data: " . date_to_php($dados["dtDesignacao"]), "pdadosComissao");

            if (!empty($dados["dtAtoDesignacao"])) {
                p("Ato do Reitor: " . date_to_php($dados["dtAtoDesignacao"]), "pdadosComissao");
            }

            if (!empty($dados["numProcDesignacao"])) {
                p("Processo: " . $dados["numProcDesignacao"], "pdadosComissao");
            }

            if (!empty($dados["dtPublicDesignacao"])) {
                if (empty($dados["pgPublicDesignacao"])) {
                    p("Publicação: " . date_to_php($dados["dtPublicDesignacao"]), "pdadosComissao");
                } else {
                    p("Publicação: " . date_to_php($dados["dtPublicDesignacao"]) . " pag:" . $dados["pgPublicDesignacao"], "pdadosComissao");
                }
            }
        } else {
            echo "---";
        }
    }

    ###########################################################

    function exibeDadosTermino($idOrdenador) {

        /**
         * Exibe todos os dados de uma designação
         */
        # Pega os dados
        $select = "SELECT dtTermino,
                          dtPublicTermino,
                          pgPublicTermino,
                          numProcTermino,
                          dtAtoTermino
                   FROM tbordenador
                  WHERE idOrdenador = {$idOrdenador}";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        if (!empty($dados["dtTermino"])) {
            p("Data: " . date_to_php($dados["dtTermino"]), "pdadosComissao");

            if (!empty($dados["dtAtoTermino"])) {
                p("Ato do Reitor: " . date_to_php($dados["dtAtoTermino"]), "pdadosComissao");
            }

            if (!empty($dados["numProcTermino"])) {
                p("Processo: " . $dados["numProcTermino"], "pdadosComissao");
            }

            if (!empty($dados["dtPublicTermino"])) {
                if (empty($dados["pgPublicTermino"])) {
                    p("Publicação: " . date_to_php($dados["dtPublicTermino"]), "pdadosComissao");
                } else {
                    p("Publicação: " . date_to_php($dados["dtPublicTermino"]) . " pag:" . $dados["pgPublicTermino"], "pdadosComissao");
                }
            }
        }else{
            echo "---";
        }
    }

    ###########################################################
}
