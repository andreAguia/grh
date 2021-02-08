<?php

class VagaAdm {

    /**
     * Classe que abriga as várias rotina do Controle de Vagas de Administrativos e Técnicos
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    public function get_dados($idConcursoVaga = null) { // integer o id da vaga
        /**
         * Retorna todos os dados arquivados na tabela tbvaga
         * 
         * @syntax $this->get_dados($idVaga);
         */

        if (vazio($idConcursoVaga)) {
            alert("Deve-se informar o idVaga!");
        } else {
            # Pega os dados
            $select = "SELECT *
                       FROM tbconcursovaga
                      WHERE idConcursoVaga = $idConcursoVaga";

            $pessoal = new Pessoal();
            return $pessoal->select($select, false);
        }
    }

    ###########################################################

    /**
     * Método get_numVagas
     * 
     * Exibe o número de svagas totais (reais e de reposição em todos os cargos de um concurso)
     */
    public function get_numVagas($idConcurso, $idTipoCargo = null) {

        $select = "SELECT (COALESCE(SUM(vagasNovas),0) + COALESCE(SUM(vagasReposicao),0))
                     FROM tbconcursovaga
                    WHERE idConcurso = {$idConcurso}";

        if (!empty($idTipoCargo)) {
            $select .= " AND tbcargo.idTipoCargo = {$idTipoCargo}";
        }

        $pessoal = new Pessoal();
        $numero = $pessoal->select($select);
        return $numero[0][0];
    }

    ###########################################################

    /**
     * Método get_CargosEVagas
     * 
     * retorna array com os cargos disponibilizados neste concurso
     */
    public function get_CargosEVagas($idConcurso) {

        $select = "SELECT idTipoCargo, (COALESCE(vagasNovas,0) + COALESCE(vagasReposicao,0))
                     FROM tbconcursovaga
                    WHERE idConcurso = {$idConcurso}";

        $pessoal = new Pessoal();
        return $pessoal->select($select);
    }

    ###########################################################

    /**
     * Método get_Cargos
     * 
     * retorna array com os cargos disponibilizados neste concurso
     */
    public function get_Cargos($idConcurso) {

        $select = "SELECT idTipoCargo
                     FROM tbconcursovaga
                    WHERE idConcurso = {$idConcurso} 
                    ORDER BY idTipoCargo";

        $pessoal = new Pessoal();
        return $pessoal->select($select);
    }

    ###########################################################

    /**
     * Método get_Cargos
     * 
     * retorna array com os cargos dos servidores empossados neste concurso para comparar com os disponibilizados
     */
    public function get_CargosEmpossados($idConcurso) {

        $select = "SELECT distinct idTipoCargo
                     FROM tbservidor LEFT JOIN tbcargo USING (idCargo)
                    WHERE idConcurso = {$idConcurso} 
                     Order BY idTipoCargo";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select);
        return $row;
    }

    ###########################################################

    /**
     * Método get_numSemCargo
     * 
     * retorna o número de servidores empossados sem cargo cadastrado neste concurso
     */
    public function get_numSemCargo($idConcurso) {

        $select = "SELECT idServidor
                     FROM tbservidor LEFT JOIN tbcargo USING (idCargo)
                    WHERE idConcurso = {$idConcurso} 
                      AND idTipoCargo is NULL";

        $pessoal = new Pessoal();
        $row = $pessoal->count($select);
        return $row;
    }

    ###########################################################

    /**
     * Método get_servidoresConcurso
     * 
     * Exibe o número de servidores ativos e Inativos em um determinado concurso
     */
    public function get_numServidoresConcurso($idConcurso, $idTipoCargo = null) {

        # Verifica se o concurso é de Adm & Tec ou se é de Professor
        $concurso = new Concurso();
        $dados = $concurso->get_dados($idConcurso);
        $tipo = $dados['tipo'];

        $select = 'SELECT tbservidor.idServidor                             
                     FROM tbservidor LEFT JOIN tbcargo USING (idCargo)';

        # Se for concurso de professor
        if ($tipo == 2) {
            $select .= ' JOIN tbvagahistorico ON (tbvagahistorico.idServidor = tbservidor.idServidor)';
        }

        $select .= ' WHERE true';

        if (!empty($idTipoCargo)) {
            $select .= " AND tbcargo.idTipoCargo = {$idTipoCargo}";
        }


        if ($tipo == 1) {
            $select .= ' AND (tbservidor.idConcurso = ' . $idConcurso . ')';
        } else {
            $select .= ' AND (tbvagahistorico.idConcurso = ' . $idConcurso . ')';
        }

        $pessoal = new Pessoal();
        $numero = $pessoal->count($select);
        return $numero;
    }

    ###########################################################

    /**
     * Método get_servidoresAtivosConcurso
     * 
     * Exibe o número de servidores ativos em um determinado vaga
     */
    public function get_servidoresVaga($idConcursoVaga) {

        # Pega os dados desta vaga
        $dados = $this->get_dados($idConcursoVaga);
        $idTipoCargo = $dados['idTipoCargo'];
        $idConcurso = $dados['idConcurso'];

        $select = "SELECT tbservidor.idServidor                             
                     FROM tbservidor JOIN tbcargo USING(idCargo)
                    WHERE tbservidor.idConcurso = {$idConcurso}
                      AND tbcargo.idTipoCargo = {$idTipoCargo}
                    ";

        $pessoal = new Pessoal();
        $numero = $pessoal->count($select);
        return $numero;
    }

    ###########################################################

    /**
     * Método get_servidoresAtivosConcurso
     * 
     * Exibe o número de servidores ativos em um determinado vaga
     */
    public function get_servidoresAtivosVaga($idConcurso = null, $idTipoCargo = null) {

        $select = "SELECT tbservidor.idServidor                             
                     FROM tbservidor JOIN tbcargo USING(idCargo)
                    WHERE situacao = 1
                      AND tbservidor.idConcurso = {$idConcurso}
                      AND tbcargo.idTipoCargo = {$idTipoCargo}
                    ";

        $pessoal = new Pessoal();
        $numero = $pessoal->count($select);
        return $numero;
    }

    ###########################################################

    /**
     * Método get_servidoresAtivosConcurso
     * 
     * Exibe o número de servidores ativos em um determinado vaga
     */
    public function get_numServidoresAtivosVaga($idConcursoVaga = null) {

        $dados = $this->get_dados($idConcursoVaga);

        $select = "SELECT tbservidor.idServidor                             
                     FROM tbservidor JOIN tbcargo USING(idCargo)
                    WHERE situacao = 1
                      AND tbservidor.idConcurso = {$dados['idConcurso']}
                      AND tbcargo.idTipoCargo = {$dados['idTipoCargo']}
                    ";

        $pessoal = new Pessoal();
        $numero = $pessoal->count($select);
        return $numero;
    }

    ###########################################################

    /**
     * Método get_servidoresAtivosConcurso
     * 
     * Exibe o número de servidores ativos em um determinado vaga
     */
    public function get_vagasDisponiveis($idConcursoVaga = null) {

        # Pega o número de vagas reais
        $dados = $this->get_dados($idConcursoVaga);

        $select = "SELECT vagasNovas
                     FROM tbconcursovaga
                    WHERE idConcurso = {$dados['idConcurso']}
                      AND idTipoCargo = {$dados['idTipoCargo']}
                    ";

        $pessoal = new Pessoal();
        $numero = $pessoal->select($select, false);

        # Pega o número de servidores Ativos nessa vaga
        $numServAtivos = $this->get_numServidoresAtivosVaga($idConcursoVaga);


        return $numero[0] - $numServAtivos;
    }

    ###########################################################

    /**
     * Método get_numVagas
     * 
     * Exibe o número de svagas totais (reais e de reposição em todos os cargos de um concurso)
     */
    public function get_numReaisCargo($idTipoCargo) {

        $select = "SELECT SUM(vagasNovas)
                     FROM tbconcursovaga
                    WHERE idTipoCargo = {$idTipoCargo}";

        $pessoal = new Pessoal();
        $numero = $pessoal->select($select, false);
        return $numero[0];
    }

    ###########################################################

    /**
     * Método get_numServidoresAtivosCargo
     * 
     * Exibe o número de servidores estatutários ativos em um determinado cargo
     */
    public function get_numServidoresAtivosCargo($idTipoCargo = null) {

        $select = "SELECT tbservidor.idServidor                             
                     FROM tbservidor JOIN tbcargo USING(idCargo)
                    WHERE situacao = 1 
                      AND idPerfil = 1
                      AND tbcargo.idTipoCargo = {$idTipoCargo}
                    ";

        $pessoal = new Pessoal();
        $numero = $pessoal->count($select);
        return $numero;
    }

    ###########################################################

    /**
     * Método get_servidoresAtivosConcurso
     * 
     * Exibe o número de servidores ativos em um determinado vaga
     */
    public function get_vagasDisponiveisCargo($idTipoCargo = null) {

        # Vagas Reais
        $vagaReais = $this->get_numReaisCargo($idTipoCargo);

        # Servidores Ativos
        $numServAtivos = $this->get_numServidoresAtivosCargo($idTipoCargo);


        return $vagaReais - $numServAtivos;
    }

    ###########################################################
}
