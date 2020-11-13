<?php

class Acumulacao {

    /**
     * Abriga as várias rotina referentes a acumulação de cargos públicos de um servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $idAcumulacao integer null O id da acumulação
     */
    private $idAcumulacao = null;

##############################################################

    public function __construct($idAcumulacao = null) {
        /**
         * Inicia a Classe somente
         * 
         * @param $idAcumulacao integer null O id da acumulação
         * 
         * @syntax $acumulacao = new Aculumacao([$idAcumulacao]);
         */
        $this->idAcumulacao = $idAcumulacao;
    }

##############################################################

    public function get_dados($idAcumulacao) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $idAcumulacao integer null O id da acumulação
         * 
         * @syntax $acumulacao->get_dados([$idAcumulacao]);
         */
        # Joga o valor informado para a variável da classe
        if (!vazio($idAcumulacao)) {
            $this->idAcumulacao = $idAcumulacao;
        }

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (vazio($this->idAcumulacao)) {
            alert("É necessário informar o id da Acumulação.");
            return;
        }

        # Pega os dados
        $select = 'SELECT * 
                     FROM tbacumulacao
                    WHERE idAcumulacao = ' . $this->idAcumulacao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row;
    }

##############################################################

    public function get_resultado($idAcumulacao) {

        /**
         * Informa o resultado final de uma acumulação
         * 
         * @param $idAcumulacao integer null O id da acumulação
         * 
         * @syntax $acumulacao->get_resultado([$idAcumulacao]);
         */
        # Joga o valor informado para a variável da classe
        if (!vazio($idAcumulacao)) {
            $this->idAcumulacao = $idAcumulacao;
        }

        # Inicia a variável de retorno
        $retorno = null;

        # Pega os dados
        $dados = $this->get_dados($this->idAcumulacao);

        # Joga os resultados nas variáveis
        $resultado = $dados["resultado"];
        $resultado1 = $dados["resultado1"];
        $resultado2 = $dados["resultado2"];
        $resultado3 = $dados["resultado3"];

        # Verifica o primeiro resultado
        if (!vazio($resultado)) {
            $retorno = $resultado;
        }

        # Verifica o primeiro recurso
        if (!vazio($resultado1)) {
            $retorno = $resultado1;
        }

        # Verifica o segundo recurso
        if (!vazio($resultado2)) {
            $retorno = $resultado2;
        }

        # Verifica o último recurso
        if (!vazio($resultado3)) {
            $retorno = $resultado3;
        }

        # Trata o retorno
        if ($retorno == 1) {
            $retorno = "<span class='label success'>Lícito</span>";
        } elseif ($retorno == 2) {
            $retorno = "<span class='label alert'>Ilícito</span>";
        } else {
            $retorno = "---";
        }

        return $retorno;
    }

##############################################################

    public function exibePublicacao($idAcumulacao) {

        /**
         * Informe os dados da Publicação de uma solicitação de Acumulação
         * 
         * @param $idAcumulacao integer null O id da acumulação
         * 
         * @syntax $acumulacao->exibePublicacao([$idAcumulacao]);
         */
        # Joga o valor informado para a variável da classe
        if (!vazio($idAcumulacao)) {
            $this->idAcumulacao = $idAcumulacao;
        }

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT dtPublicacao, pgPublicacao
                     FROM tbacumulacao
                    WHERE idAcumulacao = ' . $this->idAcumulacao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        if (is_null($row[0])) {
            $retorno = trataNulo($row[0]);
        } else {
            $retorno = date_to_php($row[0]) . "<br/>Pag.: " . trataNulo($row[1]);
        }

        return $retorno;
    }

##############################################################

    public function exibeProcesso($idAcumulacao) {

        /**
         * Informe os dados do processo de uma solicitação de Acumulação
         * 
         * @param $idAcumulacao integer null O id da acumulação
         * 
         * @syntax $acumulacao->exibeProcesso([$idAcumulacao]);
         */
        # Joga o valor informado para a variável da classe
        if (!vazio($idAcumulacao)) {
            $this->idAcumulacao = $idAcumulacao;
        }

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT processo, dtProcesso
                     FROM tbacumulacao
                    WHERE idAcumulacao = ' . $this->idAcumulacao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        if (is_null($row[0])) {
            $retorno = trataNulo($row[0]);
        } else {
            $retorno = $row["processo"] . "<br/>" . date_to_php($row["dtProcesso"]);
        }

        return $retorno;
    }

##############################################################

    public function exibeDadosOutroVinculo($idAcumulacao) {

        /**
         * Informe os dados do processo de uma solicitação de Acumulação
         * 
         * @param $idAcumulacao integer null O id da acumulação
         * 
         * @syntax $acumulacao->exibeProcesso([$idAcumulacao]);
         */
        # Joga o valor informado para a variável da classe
        if (!vazio($idAcumulacao)) {
            $this->idAcumulacao = $idAcumulacao;
        }

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT instituicao,
                          cargo,                                     
                          matricula,
                          dtAdmissao
                     FROM tbacumulacao
                    WHERE idAcumulacao = ' . $this->idAcumulacao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        p($row["instituicao"], "pgetNome");
        p($row["cargo"], "pgetCargo");
        if (!empty($row["instituicao"])) {
            p("Matrícula: {$row['matricula']} / Admissão: " . date_to_php($row['dtAdmissao']), "pgetPerfil");
        }
    }

##############################################################

    public function exibeDadosUenf($idServidor) {

        /**
         * Informe os dados do Servidor
         * 
         * @param $idServidor integer null O $idServidor
         * 
         * @syntax $acumulacao->exibeDadosUenf([$idAcumulacao]);
         */
        # Joga o valor informado para a variável da classe
        if (vazio($idServidor)) {
            return null;
        } else {
            $pessoal = new Pessoal();
            p($pessoal->get_lotacao($idServidor), "pgetNome");
            p($pessoal->get_cargo($idServidor), "pgetCargo");
            p("Matrícula: {$pessoal->get_matricula($idServidor)} / Admissão: {$pessoal->get_dtAdmissao($idServidor)}", "pgetPerfil");
        }
    }

##############################################################
}
