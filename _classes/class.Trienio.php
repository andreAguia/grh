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

    function getDados($id) {

        /**
         * fornece a próxima tarefa a ser realizada
         */
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
     * informa o valor atual do trienio de um servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    function getValor($idServidor) {

        # Conect ao banco de dados
        $pessoal = new Pessoal();

        # Pega os valores
        $salario = $pessoal->get_salarioBase($idServidor);
        $percentual = $pessoal->get_trienioPercentual($idServidor);

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
     * informa o percentual atual do trienio de um servidor
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
     * informa a período Aquisitivo de um trienio de um servidor
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
            }else{
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

}
