<?php

class Dependente {

    /**
     * Abriga as várias rotina do COntrole de parentes e dependentes
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ##############################################################

    public function get_dados($id = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $id integer null O id 
         * 
         * @syntax $dependente->get_dados([$id]);
         */
        # Joga o valor informado para a variável da classe
        if (!empty($id)) {
            $pessoal = new Pessoal();
            return $pessoal->select("SELECT * FROM tbdependente WHERE idDependente = {$id}", false);
        } else {
            return null;
        }
    }

    ###########################################################

    public function exibeNomeParentescoNascimento($id) {

        # Pega os dados
        $dados = $this->get_dados($id);
        $pessoal = new Pessoal();
        plista(
                $dados["nome"],
                "Nascimento: " . date_to_php($dados["dtNasc"]),
                $pessoal->get_parentesco($dados["idParentesco"])
        );
    }

    ###########################################################

    public function exibeNomeParentesco($id) {

        # Pega os dados
        $dados = $this->get_dados($id);
        $pessoal = new Pessoal();
        plista(
                $dados["nome"],
                $pessoal->get_parentesco($dados["idParentesco"])
        );
    }

    ###########################################################

    public function exibeNomeCpf($id) {

        # Pega os dados
        $dados = $this->get_dados($id);

        # verifica se tem cpf cadastrado
        if (empty($dados["cpf"])) {
            $cpf = null;
        } else {
            $cpf = "CPF: " . $dados["cpf"];
        }

        # Exibe o nome e o cpf (quando houver)
        plista(
                $dados["nome"],
                $cpf
        );
    }

    ###########################################################

    public function exibeNascimentoIdade($id) {

        # Pega os dados
        $dados = $this->get_dados($id);

        # Exibe os dados
        plista(
                date_to_php($dados["dtNasc"]),
                idade(date_to_php($dados["dtNasc"])) . " anos"
        );
    }

    ###########################################################

    public function exibeBotaoControleEscolaridade($id) {

        # Pega os dados
        $dados = $this->get_dados($id);

        if ($dados["auxEducacao"] == "Sim") {
            if (idade(date_to_php($dados["dtNasc"])) > 21) {
                
            } else {
                echo "---";
            }
        } else {
            echo "---";
        }
    }

    ###########################################################

    public function get_nome($id) {

        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);
            return $dados["nome"];
        }
    }

    ###########################################################

    public function get_cpf($id) {

        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);
            return $dados["cpf"];
        }
    }

    ###########################################################

    public function get_idParentesco($id) {

        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);
            return $dados["idParentesco"];
        }
    }

    ###########################################################

    public function get_idServidor($id) {

        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);

            $pessoal = new Pessoal();
            return $pessoal->get_idServidoridPessoa($dados["idPessoa"]);
        }
    }

    ###########################################################
}
