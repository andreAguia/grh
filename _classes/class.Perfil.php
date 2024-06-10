<?php

class Perfil {

    /**
     * Abriga as várias rotina do cadastro de Perfil
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    function get_dados($id) {

        /**
         * Pega os dados de um perfil
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (empty($id)) {
            return null;
        } else {
            $pessoal = new Pessoal();
            $select = "SELECT * FROM tbperfil WHERE idPerfil = {$id}";
            return $pessoal->select($select, false);
        }
    }

    ###########################################################

    public function exibe_permissoes($id) {

        /**
         * Exibe as permissões para esse idPerfil
         */
        # Pega os dados
        $dados = $this->get_dados($id);

        # Define a variável de retorno
        $retorno = null;

        # Progressao
        if ($dados["progressao"] == "Sim") {
            $retorno .= "Progressão, ";
        }

        # Trienio
        if ($dados["trienio"] == "Sim") {
            $retorno .= "Triênio, ";
        }

        # Comissao
        if ($dados["comissao"] == "Sim") {
            $retorno .= "Cargo em Comissão, ";
        }

        # Gratificação
        if ($dados["gratificacao"] == "Sim") {
            $retorno .= "Gratificação, ";
        }

        # Férias
        if ($dados["ferias"] == "Sim") {
            $retorno .= "Férias, ";
        }

        # Licenças
        if ($dados["licenca"] == "Sim") {
            $retorno .= "Licenças";
        }

        return $retorno;
    }

###########################################################
}
