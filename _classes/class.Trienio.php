<?php

class Trienio {
    /**
     * Abriga as várias rotina do Cadastro de triênio dos servidores
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

    /**
     * informa os dados de um triênio
     */
    function getDados($id) {

        # Pega os dados
        $select = "SELECT *
                     FROM tbtrienio
                    WHERE idTrienio = $id";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados;
    }

    ###########################################################

    /**
     * Método get_Valor
     * informa o último (atual) valor do trienio de um servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    function getValor($idServidor) {

        # Conect ao banco de dados
        $pessoal = new Pessoal();

        # Pega os valores
        $salario = $pessoal->get_salarioBase($idServidor);
        $percentual = $this->getPercentual($idServidor);

        return $salario * ($percentual / 100);
    }

    ###########################################################

    /**
     * Método get_percentual
     * informa o percentual atual do trienio de um servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    function getPercentual($idServidor) {

        $select = 'SELECT percentual
                     FROM tbtrienio
                    WHERE idServidor = ' . $idServidor . '
                 ORDER BY percentual desc';

        # Conecta ao banco de dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return null;
        } else {
            return $row[0];
        }
    }

    ###########################################################

    /**
     * Método exibePercentual
     * exibe o último (atual) percentual do trienio de um servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    function exibePercentual($idServidor) {

        # Pega o percentual
        $percentual = $this->getPercentual($idServidor);

        if ($percentual < 60) {
            return $percentual;
        } else {
            return "<b>" . $percentual . "</b>";
        }
    }

    ###########################################################

    /**
     * Método getDataInicial
     * informa a data Inicial de um trienio de um servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    function getDataInicial($idServidor) {
        $select = 'SELECT dtInicial
                     FROM tbtrienio
                    WHERE idServidor = ' . $idServidor . '
                 ORDER BY percentual desc';

        # Conecta ao banco de dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return null;
        } else {
            return date_to_php($row[0]);
        }
    }

    ###########################################################

    /**
     * Método getPeriodoAquisitivo
     * informa o último (atual) período Aquisitivo de um trienio de um servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    function getPeriodoAquisitivo($idServidor) {
        $select = 'SELECT dtInicioPeriodo,
                          dtFimPeriodo
                     FROM tbtrienio
                    WHERE idServidor = ' . $idServidor . '
                 ORDER BY percentual desc';

        # Conecta ao banco de dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return null;
        } else {
            return date_to_php($row[0]) . ' - ' . date_to_php($row[1]);
        }
    }

    ###########################################################

    /**
     * Método getProximoTrienio
     * informa a data do próximo triênio a contar dda última data de triênio recebido
     * 
     * somente pega-se a data do triênio Inicial
     * 
     * @param	string $idServidor idServidor do servidor
     */
    function getProximoTrienio($idServidor) {
        $select = 'SELECT dtInicial,
                          percentual
                     FROM tbtrienio
                    WHERE idServidor = ' . $idServidor . '
                 ORDER BY percentual desc';

        # Conecta ao banco de dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            $dataAdmissao = $pessoal->get_dtAdmissao($idServidor);
            return addAnos($dataAdmissao, 3);
        } else {
            if ($row[1] < 60) {
                $dataTrienio = date_to_php($row[0]);
                return addAnos($dataTrienio, 3);
            } else {
                return null;
            }
        }
    }

    ###########################################################

    /**
     * Método getNumProcesso
     * informa o número do processo de um trienio de um servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    function getNumProcesso($idServidor) {
        $select = 'SELECT numProcesso
                     FROM tbtrienio
                    WHERE idServidor = ' . $idServidor . '
                 ORDER BY percentual desc';

        # Conecta ao banco de dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return null;
        } else {
            return $row[0];
        }
    }

    ###########################################################

    /**
     * Método getPublicacao
     * informa data da publicação no DOERJ do triênio vigente (último)
     * 
     * @param	string $idServidor idServidor do servidor
     */
    function getPublicacao($idServidor) {
        $select = 'SELECT dtPublicacao
                     FROM tbtrienio
                    WHERE idServidor = ' . $idServidor . '
                 ORDER BY percentual desc';

        # Conecta ao banco de dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return null;
        } else {
            return date_to_php($row[0]);
        }
    }

    ###########################################################

    function get_obsGeral($idServidor) {

        /**
         * Informe obs do triênio de um servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        if (is_numeric($idServidor)) {

            # Pega os dados
            $select = "SELECT obsTrienio
                         FROM tbservidor
                        WHERE idServidor = {$idServidor}";

            $retorno = $pessoal->select($select, false);

            # Retorno
            return $retorno[0];
        } else {
            return $idServidor;
        }
    }

    ###########################################################

    function exibeObsGeral($idServidor) {

        # Pega os Dados
        $mensagem = $this->get_obsGeral($idServidor);

        if (!is_null($mensagem)) {
            $painel = new Callout("warning");
            $painel->abre();

            p("Observação Geral do Triênio:", "labelOcorrencias");
            p(nl2br($mensagem), "left", "f14");

            $painel->fecha();
        }
    }

    ###########################################################

    /**
     * informa os dados de um triênio
     */
    function temDireito($idServidor) {

        # A data estipulada pela lei
        $dataLimite = "31/12/2021";

        # Verifica se o id foi fornecido
        if (is_null($idServidor)) {
            return null;
        } else {
            # Banco de dados
            $pessoal = new Pessoal();
            $concurso = new Concurso();

            # Verifica se é estatutário
            if ($pessoal->get_idPerfil($idServidor) <> 1) {
                return false;
            }

            # Pega o concurso do servidor
            $idConcurso = $pessoal->get_idConcurso($idServidor);

            # Pega a data de publicação do concurso
            $dtPublicacaoEdital = $concurso->get_dtPublicacaoEdital($idConcurso);

            # Verifica qual data é mais antiga
            if (dataMenor($dataLimite, $dtPublicacaoEdital) == $dtPublicacaoEdital) {
                return true;
            } else {
                return false;
            }
        }
    }

    ###########################################################
}
