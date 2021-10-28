<?php

class Progressao {

    /**
     * Abriga as várias rotina do Cadastro de progressão e enquadramento de um servidor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    private $idServidor = null;

    ###########################################################

    /**
     * Método Construtor
     */
    public function __construct($idServidor = null) {

        $this->idServidor = $idServidor;
    }

    ###########################################################

    function get_dados($idProgressao) {

        /**
         * Fornece os todos os dados de um idProgressao
         */
        # Pega os dados
        $select = "SELECT *
                   FROM tbprogressao
                  WHERE idProgressao = $idProgressao";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados;
    }

    ###########################################################

    function get_IdClasseAtual($idServidor = null) {

        /**
         * Fornece o idClasse atual do servidor
         */
        # Troca o valor informado para a variável da classe
        if (!vazio($idServidor)) {
            $this->idServidor = $idServidor;
        }

        $select = "SELECT idClasse
                     FROM tbprogressao
                    WHERE idServidor = $this->idServidor
                 ORDER BY dtInicial desc";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        return $row[0];
    }

    ###########################################################

    function get_IdPlanoAtual($idServidor = null) {

        /**
         * Fornece o idPlano atual do servidor
         */
        # Troca o valor informado para a variável da classe
        if (!vazio($idServidor)) {
            $this->idServidor = $idServidor;
        }

        $select = "SELECT tbclasse.idPlano
                     FROM tbprogressao LEFT JOIN tbclasse USING (idCLasse)
                    WHERE idServidor = $this->idServidor
                 ORDER BY dtInicial desc";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        return $row[0];
    }

    ###########################################################

    function get_FaixaAtual($idServidor = null) {

        /**
         * Fornece o Nivel/Faixa/Padrao do servidor
         */
        # Troca o valor informado para a variável da classe
        if (!vazio($idServidor)) {
            $this->idServidor = $idServidor;
        }

        $select = "SELECT tbclasse.faixa
                     FROM tbprogressao LEFT JOIN tbclasse USING (idCLasse)
                    WHERE idServidor = $this->idServidor
                 ORDER BY dtInicial desc";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        return $row[0];
    }

    ###########################################################

    function get_dtInicialAtual($idServidor) {

        /**
         * Fornece a data Inicial da progressão atual do servidor
         */
        # Troca o valor informado para a variável da classe
        if (!vazio($idServidor)) {
            $this->idServidor = $idServidor;
        }

        $select = "SELECT dtInicial
                     FROM tbprogressao
                    WHERE idServidor = $this->idServidor
                 ORDER BY dtInicial desc";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        return date_to_php($row[0]);
    }

    ###########################################################

    function analisaServidor($idServidor) {

        /**
         * Fornece a data Inicial da progressão atual do servidor
         */
        # Troca o valor informado para a variável da classe
        if (!vazio($idServidor)) {
            $this->idServidor = $idServidor;
        }

        # Acessa os bancos
        $pessoal = new Pessoal();
        $plano = new PlanoCargos();

        ########################
        # Pega os dados do servidor
        # Pega o salário (idClasse) atual do servidor
        $idClasse = $this->get_IdClasseAtual($idServidor);

        # Pega o plano de cargos (idPlano) atual do servidor
        $idPlano = $this->get_IdPlanoAtual($idServidor);

        # Pega o cargo (idCargo) do servidor        
        $idCargo = $pessoal->get_idCargo($this->idServidor);

        ########################
        # Pega os dados da tabela
        # Pega o idCLasse da última classe possível do plano de cargos vigente para esse cargo        
        $idClasseUltimo = $plano->get_ultimoIdClasse($idCargo);

        # Pega o plano de cargos atual
        $idPlanoAtual = $plano->get_planoAtual();

        ########################
        # Verifica se tem algum salário cadastrado
        if (is_null($idClasse)) {
            $analise = "Não Tem Salário Cadastrado";
        } else {
            # Analisa se o servidor está na última classe possível
            if ($idClasse == $idClasseUltimo) {
                $analise = "Não Pode Progredir";
            } else {
                if ($idPlano <> $idPlanoAtual) {
                    $analise = "Plano ERRADO";
                } else {
                    # Pega a última progressão válida
                    $ultimaProgressao = new DateTime($this->get_ultimaProgressaoServidorVálida($idServidor));

                    # Pega a data de hoje
                    $hoje = new DateTime();

                    # Calcula o intervalo
                    $intervalo = $ultimaProgressao->diff($hoje);

                    # Verifica se já tem 4 anos ou mais
                    if ($intervalo->y >= 4) {
                        $analise = "Tem Direito a Progressão por Antiguidade";
                    } else {
                        $analise = "Aparentemente Tudo Certo";
                    }
                }
            }
        }
        return $analise;
    }

    ###########################################################

    function get_ultimaProgressaoServidorVálida($idServidor) {

        /**
         * Fornece a última progressão válida de um servidor para efeito de contagem de tempo para progressão por antiguidade
         */
        # Pega os dados
        $select = "SELECT dtInicial
                   FROM tbprogressao
                  WHERE idServidor = $idServidor 
                    AND (idTpProgressao  = 1 OR idTpProgressao  = 2 OR idTpProgressao  = 3 OR idTpProgressao  = 4 OR idTpProgressao  = 6)
                  ORDER BY dtInicial DESC";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados[0];
    }

    ###########################################################
}
