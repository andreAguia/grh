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

        $anobase = $conteudo["anobase"];
        $dtPublicacaoEdital = date_to_php($conteudo["dtPublicacaoEdital"]);
        $regime = $conteudo["regime"];
        $tipo = $conteudo["tipo"];

        if ($editar) {
            $btnEditar = new Link("Editar", "cadastroConcurso.php?fase=editar&id={$idConcurso}");
            $btnEditar->set_class('button tiny secondary');
            $btnEditar->set_id('editarVaga');
            $btnEditar->set_title('Editar o Concurso');
            $btnEditar->show();
        }

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
    public function get_nomeConcurso($idConcurso) {

        if (empty($idConcurso)) {
            return null;
        }

        # Monta o select            
        $select = 'SELECT CONCAT(tbconcurso.anoBase," - Edital: ",DATE_FORMAT(tbconcurso.dtPublicacaoEdital,"%d/%m/%Y")) as cc                
                         FROM tbconcurso
                        WHERE idConcurso = ' . $idConcurso;

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

        if (empty($idconcurso)) {
            return null;
        }

        # Monta o select            
        $select = "SELECT regime
                     FROM tbconcurso
                    WHERE idconcurso = {$idconcurso}";

        # Pega os dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);
        return $row[0];
    }

    ###########################################################

    /**
     * Método get_dtPublicacaoEdital
     * 
     * Informa a data de publicação do edital
     * 	 
     */
    public function get_dtPublicacaoEdital($idconcurso) {

        # Monta o select            
        $select = 'SELECT dtPublicacaoEdital              
                         FROM tbconcurso
                        WHERE idconcurso = ' . $idconcurso;

        # Pega os dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return null;
        } else {
            return date_to_php($row[0]);
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

        # tabela
        $tabela = new Tabela();
        $tabela->set_titulo("Professores");
        $tabela->set_conteudo($array);
        $tabela->set_label(array("Tipo", "Sem Concurso", "Com Concurso", "Total"));
        #$tabela->set_width(array(80,20));
        $tabela->set_align(array("left", "center"));
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 0,
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

        # Pega os dados deste concurso
        $dados = $this->get_dados($idConcurso);

        # Verifica se é de docentes ou administrativo
        if ($dados["tipo"] == 1) {
            # Monta o select            
            $select = "SELECT SUM(vagasNovas) as nova,
                              SUM(vagasReposicao) as repo
                         FROM tbconcursovaga
                        WHERE idConcurso = $this->idConcurso";

            # Pega os dados
            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);
            return $row[0] + $row[1];
        } else {
            # Monta o select            
            $select = "SELECT idVagaHistorico
                         FROM tbvagahistorico
                        WHERE idConcurso = $this->idConcurso";

            # Pega os dados
            $pessoal = new Pessoal();
            $row = $pessoal->count($select);
            return $row;
        }
    }

    #####################################################################################

    /**
     * Método get_numVagasConcurso
     * 
     * Informa o numero de vagas por concurso
     */
    public function get_totalVagasConcurso($idConcursoVaga) {

        # Verifica o parêmetro
        if (empty($idConcursoVaga)) {
            return null;
        }

        # Monta o select
        $select = "SELECT vagasNovas,
                          vagasReposicao
                      FROM tbconcursovaga
                     WHERE idConcursoVaga = {$idConcursoVaga}";

        # Pega os dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        return $row[0] + $row[1];
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

    /**
     * Método sincronizaIdConcurso
     * 
     * Sincroniza idConcurso da tbServidor com idConcurso da tbvagahistorico
     */
    public function sincronizaIdConcurso() {

        # Monta o select            
        $select = "SELECT idServidor
                     FROM tbservidor JOIN tbvagahistorico USING (idServidor)
                    WHERE (idCargo = 128 OR idCargo = 129)
                      AND (idPerfil = 1 OR idPerfil = 4)
                      AND (tbservidor.idConcurso IS NULL OR tbservidor.idConcurso <> tbvagahistorico.idConcurso)
                 ORDER BY tbvagahistorico.idConcurso;";

        # Pega os dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select);
        $count = $pessoal->count($select);

        $vaga = new Vaga();

        if ($count > 0) {
            foreach ($row as $tt) {

                $novoIdConcurso = $vaga->get_idConcursoProfessor($tt[0]);

                # Grava na tabela
                $campos = array("idConcurso");
                $valor = array($novoIdConcurso);
                $pessoal->gravar($campos, $valor, $tt[0], "tbservidor", "idServidor", false);
            }
        }

        return $count;
    }

    ###########################################################

    public function exibeQuadroServidoresConcursoPorCargo($idConcurso) {
        /**
         * Exibe um quadro com os docentes sem concurso
         * 
         * @syntax $plano->exibeQuadroDocentesSemConcurso();
         */
        $select = "SELECT distinct tbtipocargo.sigla,
                          (SELECT COUNT(idServidor) FROM tbservidor JOIN tbcargo USING (idCargo) WHERE situacao = 1 AND idTipoCargo = tt.idTipoCargo AND idConcurso = {$idConcurso}),
                          (SELECT COUNT(idServidor) FROM tbservidor JOIN tbcargo USING (idCargo) WHERE situacao <> 1 AND idTipoCargo = tt.idTipoCargo AND idConcurso = {$idConcurso}),
                          (SELECT COUNT(idServidor) FROM tbservidor JOIN tbcargo USING (idCargo) WHERE idTipoCargo = tt.idTipoCargo AND idConcurso = {$idConcurso})
                     FROM tbservidor JOIN tbcargo as tt USING (idCargo) 
                                     JOIN tbtipocargo USING (idTipoCargo) 
                    WHERE idConcurso = {$idConcurso}
                 ORDER BY 1 DESC";

        $pessoal = new Pessoal();
        $conteudo = $pessoal->select($select);

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conteudo);
        $tabela->set_titulo("Servidores Empossados");
        $tabela->set_label(array("Cargo", "Ativos", "Inativos", "Total"));
        #$tabela->set_width(array(30, 15, 15, 15, 15));
        $tabela->set_align(array("left"));

        $tabela->set_colunaSomatorio([1, 2, 3]);
        $tabela->set_textoSomatorio("Total:");
        $tabela->set_totalRegistro(false);

        $tabela->show();
    }

    ###########################################################

    public function exibePublicacoesServidor($idServidor) {

        # Monta o select
        $select = "SELECT dtPublicConcursoResultado,
                          pgPublicConcursoResultado,
                          dtPublicConvocacao,
                          pgPublicConvocacao,
                          dtPublicResultadoExameMedico,
                          pgPublicResultadoExameMedico,
                          dtPublicAtoNomeacao,
                          pgPublicAtoNomeacao,
                          dtPublicAtoInvestidura,
                          pgPublicAtoInvestidura,
                          dtPublicTermoPosse,
                          pgPublicTermoPosse
                     FROM tbservidor
                     WHERE idServidor = {$idServidor}";

        $pessoal = new Pessoal();
        $conteudo = $pessoal->select($select, false);

        # Resultado do Concurso
        if (!empty($conteudo[0])) {
            p("Result. Concurso:", "pLinha4");
            p(date_to_php($conteudo[0]) . " (p" . trataNulo($conteudo[1] . ")"), "pLinha1");
            hr("grosso");
        }

        # Convocação
        if (!empty($conteudo[2])) {
            p("Convocação:", "pLinha4");
            p(date_to_php($conteudo[2]) . " (p" . trataNulo($conteudo[3] . ")"), "pLinha1");
            hr("grosso");
        }

        # Resultado do Exame Médico
        if (!empty($conteudo[4])) {
            p("Result. Exame Médico:", "pLinha4");
            p(date_to_php($conteudo[4]) . " (p" . trataNulo($conteudo[5] . ")"), "pLinha1");
            hr("grosso");
        }

        # Nomeação
        if (!empty($conteudo[6])) {
            p("Ato Nomeação:", "pLinha4");
            p(date_to_php($conteudo[6]) . " (p" . trataNulo($conteudo[7] . ")"), "pLinha1");
            hr("grosso");
        }

        # Ato de investidura
        if (!empty($conteudo[8])) {
            p("Ato Investidura: ", "pLinha4");
            p(date_to_php($conteudo[8]) . " (p" . trataNulo($conteudo[9] . ")"), "pLinha1");
            hr("grosso");
        }

        # Termo de Posse
        if (!empty($conteudo[10])) {
            p("Termo de Posse: ", "pLinha4");
            p(date_to_php($conteudo[10]) . " (p" . trataNulo($conteudo[11] . ")"), "pLinha1");
        }
    }

    ###########################################################

    public function exibeOcupanteAnterior($idServidor) {

        # Monta o select
        $select = "SELECT idServidorOcupanteAnterior
                     FROM tbservidor
                    WHERE idServidor = {$idServidor}";

        $pessoal = new Pessoal();
        $conteudo = $pessoal->select($select, false);

        $idOcupanteA = $conteudo[0];

        if (is_null($idOcupanteA)) {
            return null;
        }

        if ($idOcupanteA == 0) {
            return "primeiro servidor a ocupar a vaga";
        }

        if (is_numeric($idOcupanteA)) {
            $pessoal->get_nomeELotacaoESituacaoEAdmissao($idOcupanteA);
            hr("grosso");
            $this->get_concurso($idOcupanteA);
        }
    }

    ###########################################################

    public function exibeServidorEConcurso($idServidor) {

        # trata o id
        if (empty($idServidor)) {
            return null;
        } else {
            $pessoal = new Pessoal();
            $pessoal->get_nomeELotacaoESituacaoEAdmissao($idServidor);
            hr("grosso");
            $this->get_concurso($idServidor);
        }
    }

    ###########################################################

    public function get_idOcupantePosterior($idServidor) {

        /**
         * Informa (Sim / Não) se a vaga deste concursado inativo foi preenchida por outro servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        if (empty($idServidor)) {
            return null;
        }

        # Pega array com os dias publicados
        $select = "SELECT idServidorOcupanteAnterior,
                          idServidor
                     FROM tbservidor
                    WHERE idServidorOcupanteAnterior = {$idServidor}";

        $conteudo = $pessoal->select($select, false);
        if (empty($conteudo)) {
            return null;
        } else {
            return $conteudo["idServidor"];
        }
    }

    ###########################################################

    public function exibeOcupantePosterior($idServidor = null) {

        /**
         * Informa o ocupante posterior
         */
        
        
        if (empty($idServidor)) {
            return null;
        }
        
        # Pega o id
        $idOcupante = $this->get_idOcupantePosterior($idServidor);

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        if ($pessoal->get_idSituacao($idServidor) == 1) {
            echo "---";
        } else {
            if (empty($idOcupante)) {
                span("Não", "vermelho");
            } else {
                $pessoal->get_nomeELotacaoESituacaoEAdmissao($idOcupante);
                hr("grosso");
                $this->get_concurso($idOcupante);
            }
        }
    }

    ###########################################################

    public function exibeOcupantePosteriorPosterior($idServidor = null) {
        
        /**
         * Informa o ocupante posterior
         */
        if (empty($idServidor)) {
            return null;
        }
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega o id
        $idOcupante = $this->get_idOcupantePosterior($idServidor);
        
        if (empty($idOcupante)) {
            return null;
        }
        
        if ($pessoal->get_idSituacao($idServidor) == 1) {
            echo "---";
        } elseif ($pessoal->get_idSituacao($idOcupante) == 1) {
            echo "---";
        } else {
            if (empty($idOcupante)) {
                span("Não", "vermelho");
            } else {
                $idOcupante2 = $this->get_idOcupantePosterior($idOcupante);

                if (empty($idOcupante2)) {
                    span("Não", "vermelho");
                } else {
                    $pessoal->get_nomeELotacaoESituacaoEAdmissao($idOcupante2);
                    hr("grosso");
                    $this->get_concurso($idOcupante2);
                }
            }
        }
    }

    ##########################################################

    /**
     * Método get_tipo
     * 
     * Informa o tipo do concurso
     */
    public function get_tipo($idconcurso) {

        # Monta o select            
        $select = "SELECT tipo 
                     FROM tbconcurso
                    WHERE idconcurso = {$idconcurso}";

        if (empty($idconcurso)) {
            return null;
        } else {
            # Pega os dados
            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);

            if (empty($row[0])) {
                return null;
            } else {
                return $row[0];
            }
        }
    }

    ###########################################################

    /**
     * Método exibeMenu
     * 
     * Informa o tipo do concurso
     */
    public function exibeMenu($idConcurso, $ressaltado = null) {

        # Classes
        $concurso = new Concurso($idConcurso);
        $pessoal = new Pessoal();

        # Variáveis
        $ativos = $this->get_numServidoresAtivosConcurso($idConcurso);
        $inativos = $this->get_numServidoresInativosConcurso($idConcurso);
        $vagas = $concurso->get_numVagasConcurso($idConcurso);
        $publicacao = $concurso->get_numPublicacaoConcurso($idConcurso);
        $tipo = $concurso->get_tipo($idConcurso);

        # Monta o array
        if ($tipo == 1) {   // Administrativo
            $itensMenu = [
                ["Classificação", "cadastroConcursoAdm.php?fase=aguardaClassificacao"],
                ["Publicações", "cadastroConcursoPublicacao.php", $publicacao],
                ["Vagas", "cadastroConcursoVagaAdm.php", $vagas],
                ["Servidores Ativos", "cadastroConcursoAdm.php?fase=aguardaListaServidoresAtivos", $ativos],
                ["Servidores Inativos", "cadastroConcursoAdm.php?fase=aguardaListaServidoresInativos", $inativos],
                ["Todos os Servidores", "cadastroConcursoAdm.php?fase=aguardaListaServidoresTodos", $ativos + $inativos],
            ];
        } else {            // Professor
            $itensMenu = [
                ["Publicações", "cadastroConcursoPublicacao.php", $publicacao],
                ["Vagas", "cadastroConcursoVagaProf.php", $vagas],
                ["Servidores Ativos", "cadastroConcursoProf.php?fase=aguardaListaServidoresAtivos", $ativos],
                ["Servidores Inativos", "cadastroConcursoProf.php?fase=aguardaListaServidoresInativos", $inativos],
            ];
        }

        $painel = new Callout();
        $painel->abre();

        # Inicia o Menu de Cargos                
        $menu = new Menu("menuProcedimentos");
        $menu->add_item('titulo', 'Menu');

        foreach ($itensMenu as $item) {

            # Verifica se tem ressaltado
            if ($item[0] == $ressaltado) {
                $item[0] = "<b>{$item[0]}</b>";
            }

            # Verifica se tem contagem
            if (empty($item[2])) {
                $menu->add_item('link', $item[0], $item[1]);
            } else {
                $menu->add_item('link', "{$item[0]} ({$item[2]})", $item[1]);
            }
        }

        $menu->show();

        $painel->fecha();
    }

    ###########################################################

    public function exibeClassificacaoServidor($idServidor) {

        # Monta o select
        $select = "SELECT classificacaoConcurso,
                          instituicaoConcurso
                     FROM tbservidor
                    WHERE idServidor = {$idServidor}";

        $pessoal = new Pessoal();
        $conteudo = $pessoal->select($select, false);

        # Classificação
        if (!empty($conteudo[0])) {
            p($conteudo[0], "pLinha1");
        }

        # Instituição
        if (!empty($conteudo[1])) {

            if ($conteudo[1] == "Fenorte") {
                label($conteudo[1], "success");
            }
            if ($conteudo[1] == "Tecnorte") {
                label($conteudo[1], "warning");
            }
            if ($conteudo[1] == "Uenf") {
                label($conteudo[1], "primary");
            }

            # secondary | primary | success | warning | alert
        }
    }

    #####################################################################################

    /**
     * Método get_centroVagas
     * 
     * Informa o centros com vagas neste concurso
     */
    public function get_centroVagas($idConcurso = null) {

        # Verifica se tem $idConcurso
        if (empty($idConcurso)) {
            return null;
        } else {
            # select            
            $select = "SELECT distinct centro
                         FROM tbconcurso LEFT JOIN tbvagahistorico USING (idConcurso)
                                         LEFT JOIN tbvaga USING (idVaga)
                        WHERE tbconcurso.idConcurso = {$idConcurso}";

            # Pega os dados
            $pessoal = new Pessoal();
            $row = $pessoal->select($select);

            foreach ($row as $item) {
                #p($item["centro"],"pconcursadoNaoAtivo");
                echo $item["centro"], " ";
            }
        }
    }

    ###########################################################

    /**
     * Método get_numServidoresAtivosConcursoCargo
     * 
     * Exibe o número de servidores ativos em um determinado concurso
     */
    public function get_numServidoresAtivosConcursoCargo($texto) {

        # Divide o texto TIPO&ID
        $pedaco = explode("&", $texto);

        # Divide o texto TIPO&ID
        $pedaco = explode("&", $texto);

        # Pega os pedaços
        $idConcurso = $pedaco[0];
        $idTipoCargo = $pedaco[1];

        # Verifica se foi informado o id
        if (empty($idConcurso)) {
            return null;
        }

        # Verifica se o concurso é de Adm & Tec ou se é de Professor
        $dados = $this->get_dados($idConcurso);
        $tipo = $dados['tipo'];

        # A princípio só funciona para concurso adm
        if ($tipo == 2) { // Retira os professores
            return null;
        } else {
            $select = "SELECT tbservidor.idServidor                             
                         FROM tbservidor JOIN tbcargo USING (idCargo)
                        WHERE situacao = 1
                          AND tbcargo.idTipoCargo = {$idTipoCargo}
                          AND tbservidor.idConcurso = {$idConcurso}";

            # Pega os dados
            $pessoal = new Pessoal();
            $numero = $pessoal->count($select);

            return $numero;
        }
    }

    ###########################################################

    /**
     * Método get_numServidoresAtivosConcurso
     * 
     * Exibe o número de servidores ativos em um determinado concurso
     */
    public function get_numServidoresAtivosConcurso($idConcurso) {

        # Verifica se foi informado o id
        if (empty($idConcurso)) {
            return null;
        }

        # Verifica se o concurso é de Adm & Tec ou se é de Professor
        $dados = $this->get_dados($idConcurso);
        $tipo = $dados['tipo'];

        $select = 'SELECT tbservidor.idServidor                             
                     FROM tbservidor';

        # Se for concurso de professor
        if ($tipo == 2) {
            $select .= ' JOIN tbvagahistorico ON (tbvagahistorico.idServidor = tbservidor.idServidor)';
        }

        $select .= ' WHERE situacao = 1';

        if ($tipo == 1) {
            $select .= ' AND (tbservidor.idConcurso = ' . $idConcurso . ')';
        } else {
            $select .= ' AND (tbvagahistorico.idConcurso = ' . $idConcurso . ')';
        }

        # Pega os dados
        $pessoal = new Pessoal();
        $numero = $pessoal->count($select);

        return $numero;
    }

    ###########################################################

    /**
     * Método get_numServidoresInativosConcurso
     * 
     * Exibe o número de servidores inativos em um determinado concurso
     */
    public function get_numServidoresInativosConcurso($idConcurso) {

        # Verifica se foi informado o id
        if (empty($idConcurso)) {
            return null;
        }

        # Verifica se o concurso é de Adm & Tec ou se é de Professor
        $dados = $this->get_dados($idConcurso);
        $tipo = $dados['tipo'];

        $select = 'SELECT tbservidor.idServidor                             
                     FROM tbservidor';

        # Se for concurso de professor
        if ($tipo == 2) {
            $select .= ' JOIN tbvagahistorico ON (tbvagahistorico.idServidor = tbservidor.idServidor)';
        }

        $select .= ' WHERE situacao <> 1';

        if ($tipo == 1) {
            $select .= ' AND (tbservidor.idConcurso = ' . $idConcurso . ')';
        } else {
            $select .= ' AND (tbvagahistorico.idConcurso = ' . $idConcurso . ')';
        }

        # Pega os dados
        $pessoal = new Pessoal();
        $numero = $pessoal->count($select);

        return $numero;
    }

    ###########################################################

    /**
     * Método get_numServidoresConcurso
     * 
     * Exibe o número de servidores ativos e Inativos em um determinado concurso
     */
    public function get_numServidoresConcurso($idConcurso) {

        # Verifica se foi informado o id
        if (empty($idConcurso)) {
            return null;
        }

        # Verifica se o concurso é de Adm & Tec ou se é de Professor
        $dados = $this->get_dados($idConcurso);
        $tipo = $dados['tipo'];

        $select = 'SELECT tbservidor.idServidor                             
                     FROM tbservidor';

        # Se for concurso de professor
        if ($tipo == 2) {
            $select .= ' JOIN tbvagahistorico ON (tbvagahistorico.idServidor = tbservidor.idServidor)';
        }

        $select .= ' WHERE true';

        if ($tipo == 1) {
            $select .= ' AND (tbservidor.idConcurso = ' . $idConcurso . ')';
        } else {
            $select .= ' AND (tbvagahistorico.idConcurso = ' . $idConcurso . ')';
        }

        # Pega os dados
        $pessoal = new Pessoal();
        $numero = $pessoal->count($select);

        return $numero;
    }

###########################################################

    public function exibeObs($idServidor) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = 'SELECT obsConcurso
                     FROM tbservidor
                    WHERE idServidor = ' . $idServidor;

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno[0]);
        }
    }

###########################################################

    public function exibeObsRel($idServidor) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = 'SELECT obsConcurso
                     FROM tbservidor
                    WHERE idServidor = ' . $idServidor;

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            return $retorno[0];
        }
    }

###########################################################

    /**
     * Método get_concurso
     * Informa o concurso do servidor
     * 
     * @param string $idServidor    null idServidor do servidor
     */
    public function get_concurso($idServidor) {

        # Verifica se  id foi informado
        if (empty($idServidor)) {
            return null;
        } else {
            # Conecta ao Banco de Dados
            $pessoal = new Pessoal();

            # Pega o cargo do servidor
            $select = 'SELECT anobase
                     FROM tbconcurso LEFT JOIN tbservidor USING (idConcurso)
                    WHERE idServidor = ' . $idServidor;

            $row = $pessoal->select($select, false);

            if (empty($row[0])) {
                return null;
            }

            pLista(
                    "Concurso de {$row[0]}",
                    $pessoal->get_cargo($idServidor)
            );
        }
    }

    ###########################################################

    /**
     * Método rel_ServidoresPorAno
     * exibe o relatório por ano
     * 
     * 
     */
    public function rel_ServidoresPorAno($ano) {

        # Ver servidores ativos
        $servAtivos = new Link(null, "../grhRelatorios/historico.servidores.ativos.porAno2.php?parametroAno={$ano}");
        $servAtivos->set_imagem(PASTA_FIGURAS_GERAIS . 'olho.png', 20, 20);
        $servAtivos->set_title("Exibe os servidores ativos");
        $servAtivos->set_target("_blank");
        $servAtivos->show();
    }

    ###########################################################
}
