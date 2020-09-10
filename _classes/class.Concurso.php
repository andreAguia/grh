<?php

class Concurso {

    /**
     * Abriga as várias rotina referentes a concurso
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $idConcurso integer null O id do concurso
     */
    private $idConcurso = null;

##############################################################

    public function __construct($idConcurso = null) {
        /**
         * Inicia a Classe somente
         * 
         * @param $idConcurso integer null O id do concurso
         * 
         * @syntax $concurso = new Concurso([$idConcurso]);
         */
        $this->idConcurso = $idConcurso;
    }

##############################################################

    public function get_dados($idConcurso = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $idConcurso integer null O id do concurso
         * 
         * @syntax $concurso->get_dados([$idConcurso]);
         */
        # Joga o valor informado para a variável da classe
        if (!vazio($idConcurso)) {
            $this->idConcurso = $idConcurso;
        }

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (vazio($this->idConcurso)) {
            alert("É necessário informar o id do Concurso.");
            return;
        }

        # Pega os dados
        $select = 'SELECT * 
                     FROM tbconcurso
                    WHERE idConcurso = ' . $this->idConcurso;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row;
    }

###########################################################

    /**
     * Método exibeDadosConcurso
     * fornece os dados de uma vaga em forma de tabela
     * 
     * @param	string $idVaga O id da vaga
     */
    function exibeDadosConcurso($idConcurso = null, $editar = false) {

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        # Joga o valor informado para a variável da classe
        if (!vazio($idConcurso)) {
            $this->idConcurso = $idConcurso;
        }

        $conteudo = $this->get_dados($idConcurso);

        $painel = new Callout("primary");
        $painel->abre();

        $btnEditar = new Link("Editar", "?fase=editardeFato&id=" . $this->idConcurso);
        $btnEditar->set_class('button tiny secondary');
        $btnEditar->set_id('editarVaga');
        $btnEditar->set_title('Editar o Concurso');
        $btnEditar->show();

        $anobase = $conteudo["anobase"];
        $dtPublicacaoEdital = date_to_php($conteudo["dtPublicacaoEdital"]);
        $regime = $conteudo["regime"];
        $tipo = $conteudo["tipo"];

        # trata o tipo
        if ($tipo == 1) {
            $tipo = "Adm & Tec";
        } elseif ($tipo == 2) {
            $tipo = "Professor";
        }

        p(" Concurso de", "vagaCargo");
        p($anobase, "vagaCentro");
        p($tipo . " - " . $regime, "vagaCargo");
        p($dtPublicacaoEdital, "vagaCargo");

        $painel->fecha();
    }

    ###########################################################

    /**
     * Método get_nomeConcurso
     * 
     * Informa o nome de um idconcurso	 */
    public function get_nomeConcurso($idconcurso) {

        # Monta o select            
        $select = 'SELECT CONCAT(tbconcurso.anoBase," - Edital: ",DATE_FORMAT(tbconcurso.dtPublicacaoEdital,"%d/%m/%Y")) as cc                
                         FROM tbconcurso
                        WHERE idconcurso = ' . $idconcurso;

        # Pega os dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);
        return $row[0];
    }

    ###########################################################

    /**
     * Método get_regime
     * 
     * Informa o nome de um idconcurso	 */
    public function get_regime($idconcurso) {

        # Monta o select            
        $select = 'SELECT regime
                         FROM tbconcurso
                        WHERE idconcurso = ' . $idconcurso;
        if (empty($idconcurso)) {
            return null;
        } else {
            # Pega os dados
            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);
            return $row[0];
        }
    }

    ###########################################################

    public function exibeQuadroDocentesSemConcurso() {
        /**
         * Exibe um quadro com os docentes sem concurso
         * 
         * @syntax $plano->exibeQuadroDocentesSemConcurso();
         */
        $ativosS = $this->get_numDocentesAtivosSemConcurso();
        $inativosS = $this->get_numDocentesInativosSemConcurso();
        $totals = $ativosS + $inativosS;

        $ativosC = $this->get_numDocentesAtivosComConcurso();
        $inativosC = $this->get_numDocentesInativosComConcurso();
        $totalc = $ativosC + $inativosC;

        # conteúdo
        $array = array(array("Ativos", $ativosS, $ativosC, $ativosS + $ativosC),
            array("Inativos", $inativosS, $inativosC, $inativosS + $inativosC),
            array("Total", $totals, $totalc, $totals + $totalc));


        # Exemplo de tabela simples
        $tabela = new Tabela();
        $tabela->set_titulo("Professores");
        $tabela->set_conteudo($array);
        $tabela->set_label(array("Tipo", "Sem Concurso", "Com Concurso", "Total"));
        #$tabela->set_width(array(80,20));
        $tabela->set_align(array("left", "center"));
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                'valor' => "Total",
                'operador' => '=',
                'id' => 'totalVagas')));
        $tabela->show();
    }

    #####################################################################################

    /**
     * Método get_numDocentesAtivosSemConcurso
     * 
     * Informa o nome de um idconcurso	 */
    public function get_numDocentesAtivosSemConcurso() {

        # Monta o select            
        $select = 'SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbvagahistorico USING (idServidor)
                        WHERE tbvagahistorico.idConcurso is null
                          AND tbservidor.situacao = 1
                          AND (idPerfil = 1 OR idPerfil = 4)
                          AND (idCargo = 128 OR idCargo = 129)';

        # Pega os dados
        $pessoal = new Pessoal();
        $row = $pessoal->count($select);
        return $row;
    }

    #####################################################################################

    /**
     * Método get_numDocentesInativosSemConcurso
     * 
     * Informa o nome de um idconcurso	 */
    public function get_numDocentesInativosSemConcurso() {

        # Monta o select            
        $select = 'SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbvagahistorico USING (idServidor)
                        WHERE tbvagahistorico.idConcurso is null
                          AND tbservidor.situacao <> 1
                          AND (idPerfil = 1 OR idPerfil = 4)
                          AND (idCargo = 128 OR idCargo = 129)';

        # Pega os dados
        $pessoal = new Pessoal();
        $row = $pessoal->count($select);
        return $row;
    }

    #####################################################################################

    /**
     * Método get_numDocentesAtivosSemConcurso
     * 
     * Informa o nome de um idconcurso	 */
    public function get_numDocentesAtivosComConcurso() {

        # Monta o select            
        $select = 'SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbvagahistorico USING (idServidor)
                        WHERE tbvagahistorico.idConcurso is NOT null
                          AND tbservidor.situacao = 1
                          AND (idPerfil = 1 OR idPerfil = 4)
                          AND (idCargo = 128 OR idCargo = 129)';

        # Pega os dados
        $pessoal = new Pessoal();
        $row = $pessoal->count($select);
        return $row;
    }

    #####################################################################################

    /**
     * Método get_numDocentesAtivosSemConcurso
     * 
     * Informa o nome de um idconcurso	 */
    public function get_numDocentesInativosComConcurso() {

        # Monta o select            
        $select = 'SELECT tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbvagahistorico USING (idServidor)
                        WHERE tbvagahistorico.idConcurso is NOT null
                          AND tbservidor.situacao <> 1
                          AND (idPerfil = 1 OR idPerfil = 4)
                          AND (idCargo = 128 OR idCargo = 129)';

        # Pega os dados
        $pessoal = new Pessoal();
        $row = $pessoal->count($select);
        return $row;
    }

    #####################################################################################

    /**
     * Método get_numVagasConcurso
     * 
     * Informa o numero de vagas por concurso
     */
    public function get_numVagasConcurso($idConcurso) {

        # Joga o valor informado para a variável da classe
        if (!vazio($idConcurso)) {
            $this->idConcurso = $idConcurso;
        }

        # Monta o select            
        $select = "SELECT idVagaHistorico
                         FROM tbvagahistorico
                        WHERE idConcurso = $this->idConcurso";

        # Pega os dados
        $pessoal = new Pessoal();
        $row = $pessoal->count($select);
        return $row;
    }

    #####################################################################################

    /**
     * Método get_numPublicacaoConcurso
     * 
     * Informa o numero de publicação por concurso
     */
    public function get_numPublicacaoConcurso($idConcurso) {

        # Joga o valor informado para a variável da classe
        if (!vazio($idConcurso)) {
            $this->idConcurso = $idConcurso;
        }

        # Monta o select            
        $select = "SELECT idConcursoPublicacao
                         FROM tbconcursopublicacao
                        WHERE idConcurso = $this->idConcurso";

        # Pega os dados
        $pessoal = new Pessoal();
        $row = $pessoal->count($select);
        return $row;
    }

    #####################################################################################
}
