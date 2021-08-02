<?php

class Aposentadoria {

    /**
     * Abriga as várias rotina referentes a aposentadoria do servidor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
##############################################################################################################################################

    function get_numServidoresAposentados() {

        /**
         * informa o número de Servidores Ativos
         * 
         * @param integer $idPessoa do servidor
         */
        $select = 'SELECT idServidor
                     FROM tbservidor
                    WHERE situacao = 2
                    AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)';

        $pessoal = new Pessoal();
        $count = $pessoal->count($select);
        return $count;
    }

##############################################################################################################################################

    function exibeAposentadosPorAno($parametroAno = null) {

        /**
         * Exibe tabela com os aposentados por ano de aposentadoria
         * 
         * @param integer $parametroAno da aposentadoria
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Trata o parametro do ano
        if (is_null($parametroAno)) {
            $parametroAno = date('Y');
        }

        # Monta o select
        $select = 'SELECT tbservidor.idfuncional,
                              tbservidor.idServidor,
                              tbservidor.dtAdmissao,
                              tbservidor.dtDemissao,
                              tbmotivo.motivo
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                        WHERE YEAR(tbservidor.dtDemissao) = "' . $parametroAno . '"
                          AND situacao = 2
                          AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                     ORDER BY dtDemissao';

        $result = $pessoal->select($select);

        $tabela = new Tabela();
        $tabela->set_titulo('Servidores Estatutários / Celetistas Aposentados em ' . $parametroAno);
        $tabela->set_tituloLinha2('Com Informaçao de Contatos');
        $tabela->set_subtitulo('Ordenado pela Data de Saída');

        $tabela->set_label(array('IdFuncional', 'Servidor', 'Admissão', 'Saída', 'Motivo'));
        $tabela->set_align(array('center', 'left', 'center', 'center', 'left'));
        $tabela->set_funcao(array(null, null, "date_to_php", "date_to_php"));

        $tabela->set_classe(array(null, "pessoal"));
        $tabela->set_metodo(array(null, "get_nomeECargo"));

        $tabela->set_conteudo($result);

        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editarAno');
        $tabela->show();
    }

##############################################################################################################################################

    function exibeAposentadosPorTipo($parametroMotivo = null) {

        /**
         * Exibe tabela com os aposentados por tipo de aposentadoria
         * 
         * @param string $parametroMotivo da aposentadoria
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Monta o select
        $select = 'SELECT tbservidor.idfuncional,
                              tbservidor.idServidor,
                              tbservidor.dtAdmissao,
                              tbservidor.dtDemissao,
                              tbservidor.idServidor
                         FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                         LEFT JOIN tbmotivo on (tbservidor.motivo = tbmotivo.idMotivo)
                        WHERE tbservidor.motivo = ' . $parametroMotivo . '
                          AND situacao = 2
                          AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                     ORDER BY dtDemissao';

        $result = $pessoal->select($select);

        $tabela = new Tabela();
        $tabela->set_titulo('Servidores Estatutários / Celetistas Aposentados por Tipo');
        $tabela->set_tituloLinha2('Com Informaçao de Contatos');
        $tabela->set_subtitulo('Ordenado pela Data de Saída');

        $tabela->set_label(array('IdFuncional', 'Servidor', 'Admissão', 'Saída', 'Perfil'));
        $tabela->set_align(array('center', 'left'));
        $tabela->set_funcao(array(null, null, "date_to_php", "date_to_php"));

        $tabela->set_classe(array(null, "pessoal", null, null, "pessoal"));
        $tabela->set_metodo(array(null, "get_nomeECargo", null, null, "get_perfil"));

        $tabela->set_conteudo($result);

        $tabela->set_idCampo('idServidor');
        $tabela->set_editar('?fase=editarMotivo');
        $tabela->show();
    }

##############################################################################################################################################
#    Previsão de Aposentadoria de Ativo
##############################################################################################################################################    

    /**
     * Método get_tempoServicoUenf
     * informa o total de dias corridos de tempo de serviço dentro da uenf
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_tempoServicoUenf($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Data de admissão
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);   # Data de entrada na UENF
        # Define a data inicial
        $dtInicial = date_to_bd($dtAdmissao);

        # Verifica se o servidor é inativo e pega a data de saída dele
        $dtSaida = $pessoal->get_dtSaida($idServidor);

        $dtHoje = date("Y-m-d");

        # Define a data término
        if (!vazio($dtSaida)) {       // Se tem saída é a saída
            $dtFinal = $dtSaida;
        } else {
            $dtFinal = $dtHoje;     // Se não tiver saída é hoje
        }

        # Calcula a diferença em segundos entre as datas
        $diferenca = strtotime($dtFinal) - strtotime($dtInicial);

        # Calcula a diferença em dias
        $dias = floor($diferenca / (60 * 60 * 24));

        return $dias;
    }

##############################################################################################################################################

    /**
     * Método get_tempoAverbadoPublico
     * informa o total de dias de tempo averbado em empresa Pública
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoAverbadoPublico($idServidor) {
        $select = 'SELECT SUM(dias) as total
                     FROM tbaverbacao
                    WHERE empresaTipo = 1 AND idServidor = ' . $idServidor . '
                         ORDER BY total';

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        $row = $pessoal->select($select, false);

        if (is_null($row[0])) {
            return 0;
        } else {
            return $row[0];
        }
    }

##############################################################################################################################################

    /**
     * Método get_tempoAverbadoPrivado
     * informa o total de dias de tempo averbado em empresa privada
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoAverbadoPrivado($idServidor) {
        $select = 'SELECT SUM(dias) as total
                     FROM tbaverbacao
                    WHERE empresaTipo = 2 AND idServidor = ' . $idServidor . '
                         ORDER BY total';

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        $row = $pessoal->select($select, false);

        if (is_null($row[0])) {
            return 0;
        } else {
            return $row[0];
        }
    }

##############################################################################################################################################

    /**
     * Método get_tempoOcorrencias
     * informa o total de dias de tempo averbado em empresa privada
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoOcorrencias($idServidor) {

        $reducao = "SELECT tbtipolicenca.nome as tipo,
                           SUM(numDias) as dias
                      FROM tblicenca JOIN tbtipolicenca USING(idTpLicenca)
                     WHERE idServidor = $idServidor
                       AND tbtipolicenca.tempoServico IS true
                  GROUP BY tbtipolicenca.nome";

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        $dados = $pessoal->select($reducao);

        # Somatório
        $totalOcorrencias = array_sum(array_column($dados, 'dias'));

        return $totalOcorrencias;
    }

##############################################################################################################################################

    /**
     * Método get_tempoServicoTotal
     * informa o total geral de dias sem considerar as ocorrências
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoServicoTotal($idServidor) {

        $uenf = $this->get_tempoServicoUenf($idServidor);
        $publico = $this->get_tempoAverbadoPublico($idServidor);
        $privado = $this->get_tempoAverbadoPrivado($idServidor);
        $total = $uenf + $publico + $privado;

        return $total;
    }

##############################################################################################################################################

    function exibeAtivosPrevisao($parametroSexo = null, $parametroNome = null, $parametroLotacao = null) {

        /**
         * Exibe tabela com a previsão de aposentadoria de servidores ativos
         * 
         * @param string $parametroSexo o sexo do servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        if ($parametroLotacao == "*") {
            $parametroLotacao = null;
        }


        # Monta o select
        $select = "SELECT tbservidor.idFuncional,
                         tbpessoa.nome,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.idServidor,
                         tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                   WHERE tbservidor.situacao = 1
                     AND idPerfil = 1
                     AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND tbpessoa.sexo = '{$parametroSexo}'";

        if (!is_null($parametroLotacao)) {  // senão verifica o da classe
            if (is_numeric($parametroLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
            }
        }

        if (!is_null($parametroNome)) {
            $select .= " AND tbpessoa.nome LIKE '%{$parametroNome}%'";
        }

        $select .= " ORDER BY tbpessoa.nome";

        $result = $pessoal->select($select);

        $tabela = new Tabela();
        $tabela->set_titulo('Estatutários Ativos com Previsão para Aposentadoria - Sexo: ' . $parametroSexo);
        $tabela->set_subtitulo('Servidores do Sexo ' . $parametroSexo);
        $tabela->set_label(array('IdFuncional', 'Nome', 'Cargo', 'Integral', 'Proporcional', 'Compulsória'));
        #$tabela->set_width(array(30,15,15,15,15));
        $tabela->set_align(array("center", "left", "left"));
        $tabela->set_funcaoDepoisClasse(array(null, null, null, "marcaSePassou", "marcaSePassou", "marcaSePassou"));

        $tabela->set_classe(array(null, null, "pessoal", "Aposentadoria", "Aposentadoria", "Aposentadoria"));
        $tabela->set_metodo(array(null, null, "get_CargoSimples", "get_dataAposentadoriaIntegral", "get_dataAposentadoriaProporcional", "get_dataAposentadoriaCompulsoria"));

        $tabela->set_conteudo($result);

        if (!IS_null($parametroNome)) {
            $tabela->set_textoRessaltado($parametroNome);
        }

        $tabela->set_idCampo('idServidor');

        if ($parametroSexo == "Masculino") {
            $tabela->set_editar('?fase=editarPrevisaoM');
        } else {
            $tabela->set_editar('?fase=editarPrevisaoF');
        }
        $tabela->show();
    }

###############################################################################################################################################

    /**
     * Método get_dataAposentadoriaIntegral
     * Informa se pode aposentar de forma integral
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_dataAposentadoriaIntegral($idServidor) {

        # Idade
        $dataIdade = $this->get_dataAposentadoriaIntegralIdade($idServidor);

        # Tempo de Serviço
        $dataTempo = $this->get_dataAposentadoriaIntegralTempo($idServidor);

        $novaData = dataMaior($dataIdade, $dataTempo);

        return $novaData;
    }

##############################################################################################################################################

    /**
     * Método get_dataAposentadoriaIntegralIdade
     * Informa a data em que o servidor passa a ter idade para solicitar aposentadoria integral
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_dataAposentadoriaIntegralIdade($idServidor) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Pega o sexo do servidor
        $sexo = $pessoal->get_sexo($idServidor);

        # Define a idade que dá direito para cada gênero
        switch ($sexo) {
            case "Masculino" :
                $idade = $intra->get_variavel("aposentadoria.integral.idade.masculino");
                break;
            case "Feminino" :
                $idade = $intra->get_variavel("aposentadoria.integral.idade.feminino");
                break;
        }

        # Pega a data de nascimento (vem dd/mm/AAAA)
        $dtNasc = $pessoal->get_dataNascimento($idServidor);

        # Calcula a data
        $novaData = addAnos($dtNasc, $idade);

        return $novaData;
    }

##############################################################################################################################################

    /**
     * Método get_dataAposentadoriaIntegralTempo
     * Informa a data em que o servidor passa a ter direito a solicitar aposentadoria integral considerando o tempo de serviço
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_dataAposentadoriaIntegralTempo($idServidor) {

        # Conecta o banco de dados
        $intra = new Intra();
        $pessoal = new Pessoal();

        # Pega o sexo do servidor
        $sexo = $pessoal->get_sexo($idServidor);

        # Define a idade que dá direito para cada gênero
        switch ($sexo) {
            case "Masculino" :
                $diasAposentadoriaIntegral = $intra->get_variavel("aposentadoria.integral.tempo.masculino");
                break;
            case "Feminino" :
                $diasAposentadoriaIntegral = $intra->get_variavel("aposentadoria.integral.tempo.feminino");
                break;
        }

        # Pega os valores
        $tempoGeral = $this->get_tempoServicoTotal($idServidor);
        $ocorrencias = $this->get_tempoOcorrencias($idServidor);

        # Calcula o tempo de serviço geral
        $totalTempoGeral = $tempoGeral - $ocorrencias;

        # Dias que faltam
        $faltando = $diasAposentadoriaIntegral - $totalTempoGeral;

        # Calcula a data
        $novaData = addDias(date("d/m/Y"), $faltando, false);

        return $novaData;
    }

##############################################################################################################################################

    /**
     * Método get_dataAposentadoriaProporcional
     * Informa a data em que o servidor passa a ter direito a solicitar aposentadoria proporcional
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_dataAposentadoriaProporcional($idServidor) {

        # Tempo Público
        $dataPublico = $this->get_dataAposentadoriaProporcionalTempo($idServidor);

        # Idade
        $dataIdade = $this->get_dataAposentadoriaProporcionalIdade($idServidor);

        # Calcula a data
        $novaData = dataMaior($dataIdade, $dataPublico);

        return $novaData;
    }

    ##############################################################################################################################################  

    /**
     * Método get_dataAposentadoriaProporcionalTempo
     * Informa a data em que o servidor passa a ter direito a solicitar aposentadoria proporcional pelo Tempo de Serviço Público
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_dataAposentadoriaProporcionalTempo($idServidor) {


        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Pega o sexo do servidor
        $sexo = $pessoal->get_sexo($idServidor);

        # Tempo de Serviço Público
        $uenf = $this->get_tempoServicoUenf($idServidor);
        $publico = $this->get_tempoAverbadoPublico($idServidor);
        $ocorrencia = $this->get_tempoOcorrencias($idServidor);
        $publicoGeral = ($uenf + $publico) - $ocorrencia;

        # Verifica se o tempo público é maior o estipulado pela regra
        switch ($sexo) {
            case "Masculino" :
                $tempo = $intra->get_variavel("aposentadoria.proporcional.tempo.masculino");
                break;

            case "Feminino" :
                $tempo = $intra->get_variavel("aposentadoria.proporcional.tempo.feminino");
                break;
        }

        $faltando = $tempo - $publicoGeral;
        $dataPublico = addDias(date("d/m/Y"), $faltando, false);

        return $dataPublico;
    }

##############################################################################################################################################  

    /**
     * Método get_dataAposentadoriaProporcionalIdade
     * Informa a data em que o servidor passa a ter direito a solicitar aposentadoria proporcional considerando a idade
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_dataAposentadoriaProporcionalIdade($idServidor) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Pega o sexo do servidor
        $sexo = $pessoal->get_sexo($idServidor);

        # Define a idade       
        switch ($sexo) {
            case "Masculino" :
                $idade = $intra->get_variavel("aposentadoria.proporcional.idade.masculino");
                break;

            case "Feminino" :
                $idade = $intra->get_variavel("aposentadoria.proporcional.idade.feminino");
                break;
        }

        $dataIdade = $pessoal->get_dataIdade($idServidor, $idade);

        return $dataIdade;
    }

##############################################################################################################################################

    /**
     * Método get_dataAposentadoriaCompulsoria
     * Informa a data em que o servidor é obrigado a se aposentar
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_dataAposentadoriaCompulsoria($idServidor) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Idade obrigatória
        $idade = $intra->get_variavel("aposentadoria.compulsoria.idade");

        # Pega a data de nascimento (vem dd/mm/AAAA)
        $dtNasc = $pessoal->get_dataNascimento($idServidor);

        # Calcula a data
        $novaData = addAnos($dtNasc, $idade);

        return $novaData;
    }

##############################################################################################################################################

    function exibeRegras($relatorio = false) {

        /**
         * Exibe uma tabela com as regras da aposentadoria
         */
        # Título
        if ($relatorio) {
            p("Regra Geral", "f16", "center");
            #hr();
        } else {
            titulo("Regra Geral");
            br();
        }

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Abre o Grid
        $grid = new Grid();

        ###
        # Aposentadoria Integral        
        $grid->abreColuna(4);

        $this->exibeRegrasIntegral($relatorio);

        $grid->fechaColuna();

        ###
        # Aposentadoria Proporcional

        $grid->abreColuna(4);

        $this->exibeRegrasProporcional($relatorio);

        $grid->fechaColuna();

        ###
        # Aposentadoria Compulsória
        $grid->abreColuna(4);

        $this->exibeRegrasCompulsoria($relatorio);

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

##############################################################################################################################################

    function exibeRegrasIntegral($relatorio = false) {

        /**
         * Exibe uma tabela com as regras da aposentadoria integral
         */
        # Conecta ao Banco de Dados
        $intra = new Intra();

        # Calculos
        $diasAposentMasculino = $intra->get_variavel("aposentadoria.integral.tempo.masculino");
        $diasAposentFeminino = $intra->get_variavel("aposentadoria.integral.tempo.feminino");
        $idadeAposentMasculino = $intra->get_variavel("aposentadoria.integral.idade.masculino");
        $idadeAposentFeminino = $intra->get_variavel("aposentadoria.integral.idade.feminino");
        $tempoCargoAposentMasculino = $intra->get_variavel("aposentadoria.integral.tempo.cargo.masculino");
        $tempoCargoAposentFeminino = $intra->get_variavel("aposentadoria.integral.tempo.cargo.feminino");

        # Monta o array
        $valores = array(
            array("Feminino", $idadeAposentFeminino, dias_to_diasMesAno($diasAposentFeminino) . "<br/>($diasAposentFeminino dias)", "{$tempoCargoAposentFeminino} anos"),
            array("Masculino", $idadeAposentMasculino, dias_to_diasMesAno($diasAposentMasculino) . "<br/>($diasAposentMasculino dias)", "{$tempoCargoAposentMasculino} anos"),
        );

        if ($relatorio) {
            $relatorio = new Relatorio();
            $relatorio->set_subtitulo("Aposentadoria Integral");
            $relatorio->set_label(array('Sexo', 'Idade', 'Tempo de Serviço'));
            $relatorio->set_align(array('left'));
            $relatorio->set_conteudo($valores);
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_exibeSomatorioGeral(false);
            $relatorio->set_botaoVoltar(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->show();
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Aposentadoria Integral");
            $tabela->set_label(array('Sexo', 'Idade', 'Tempo de Serviço', 'Tempo no Cargo'));
            #$tabela->set_width(array(12,14,14,18,15,22));
            $tabela->set_totalRegistro(false);
            $tabela->set_align(array('left'));
            $tabela->set_conteudo($valores);
            $tabela->show();
        }
    }

##############################################################################################################################################

    function exibeRegrasProporcional($relatorio = false) {

        /**
         * Exibe uma tabela com as regras da aposentadoria integral
         */
        # Conecta ao Banco de Dados
        $intra = new Intra();

        # Cálculos
        $diasAposentMasculino = $intra->get_variavel("aposentadoria.proporcional.tempo.masculino");
        $diasAposentFeminino = $intra->get_variavel("aposentadoria.proporcional.tempo.feminino");
        $idadeAposentMasculino = $intra->get_variavel("aposentadoria.proporcional.idade.masculino");
        $idadeAposentFeminino = $intra->get_variavel("aposentadoria.proporcional.idade.feminino");
        $tempoCargoAposentMasculino = $intra->get_variavel("aposentadoria.proporcional.tempo.cargo.masculino");
        $tempoCargoAposentFeminino = $intra->get_variavel("aposentadoria.proporcional.tempo.cargo.feminino");

        # Monta o array
        $valores = array(
            array("Feminino", $idadeAposentFeminino, dias_to_diasMesAno($diasAposentFeminino) . "<br/>($diasAposentFeminino dias)", "{$tempoCargoAposentFeminino} anos"),
            array("Masculino", $idadeAposentMasculino, dias_to_diasMesAno($diasAposentMasculino) . "<br/>($diasAposentMasculino dias)", "{$tempoCargoAposentMasculino} anos"),
        );

        if ($relatorio) {
            $relatorio = new Relatorio();
            $relatorio->set_subtitulo("Aposentadoria Proporcional");
            $relatorio->set_label(array('Sexo', 'Idade', 'Tempo de Serviço'));
            $relatorio->set_align(array('left'));
            $relatorio->set_conteudo($valores);
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_exibeSomatorioGeral(false);
            $relatorio->set_botaoVoltar(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->show();
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Aposentadoria Proporcional");
            $tabela->set_label(array('Sexo', 'Idade', 'Tempo de Serviço', 'Tempo no Cargo'));
            #$tabela->set_width(array(12,14,14,18,15,22));
            $tabela->set_totalRegistro(false);
            $tabela->set_align(array('left'));
            $tabela->set_conteudo($valores);
            $tabela->show();
        }
    }

##############################################################################################################################################

    function exibeRegrasCompulsoria($relatorio = false) {

        /**
         * Exibe uma tabela com as regras da aposentadoria integral
         */
        # Conecta ao Banco de Dados
        $intra = new Intra();

        # Cálculos
        $idadeAposentCompulsoria = $intra->get_variavel("aposentadoria.compulsoria.idade");

        # Monta o array
        $valores = array(
            array("Feminino", $idadeAposentCompulsoria),
            array("Masculino", $idadeAposentCompulsoria));

        if ($relatorio) {
            $relatorio = new Relatorio();
            $relatorio->set_subtitulo("Aposentadoria Compulsória");
            $relatorio->set_label(array('Sexo', 'Idade'));
            $relatorio->set_align(array('left'));
            $relatorio->set_conteudo($valores);
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_exibeSomatorioGeral(false);
            $relatorio->set_botaoVoltar(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->show();
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo("Aposentadoria Compulsória");
            $tabela->set_label(array('Sexo', 'Idade'));
            $tabela->set_totalRegistro(false);
            $tabela->set_align(array('left'));
            $tabela->set_conteudo($valores);
            $tabela->show();
        }
    }

##############################################################################################################################################

    function exibeSomatorio() {

        /**
         * Exibe uma tabela com o somatório dos servidores ativos que já podem se aposentar
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Aposentadoria Integral
        $integralFerminino = $this->get_numServidoresAtivosPodemAposentarIntegral("Feminino");
        $integralMasculino = $this->get_numServidoresAtivosPodemAposentarIntegral("Masculino");
        $integralTotal = $integralFerminino + $integralMasculino;

        # Aposentadoria Proporcional
        $proporcionalFerminino = $this->get_numServidoresAtivosPodemAposentarProporcional("Feminino");
        $proporcionalMasculino = $this->get_numServidoresAtivosPodemAposentarProporcional("Masculino");
        $proporcionalTotal = $proporcionalFerminino + $proporcionalMasculino;

        # Aposentadoria Compulsória
        $compulsoriaFerminino = $this->get_numServidoresAtivosPodemAposentarCompulsoria("Feminino");
        $compulsoriaMasculino = $this->get_numServidoresAtivosPodemAposentarCompulsoria("Masculino");
        $compulsoriaTotal = $compulsoriaFerminino + $compulsoriaMasculino;

        $totalFeminino = $integralFerminino + $proporcionalFerminino + $compulsoriaFerminino;
        $totalMasculino = $integralMasculino + $proporcionalMasculino + $compulsoriaMasculino;
        $total = $integralTotal + $proporcionalTotal + $compulsoriaTotal;

        # Monta o array
        $valores = array(
            array("Feminino", $integralFerminino, $proporcionalFerminino, $compulsoriaFerminino, $totalFeminino),
            array("Masculino", $integralMasculino, $proporcionalMasculino, $compulsoriaMasculino, $totalMasculino));

        # Tabela com os valores de aposentadoria
        $tabela = new Tabela();
        $tabela->set_titulo("Somatório de Servidores Ativos que Podem se Aposentar");
        $tabela->set_label(['Sexo', 'Integral', 'Proporcional', 'Compulsória', 'Total']);
        $tabela->set_width([20, 20, 20, 20, 20]);
        $tabela->set_totalRegistro(false);
        $tabela->set_align(['left']);
        $tabela->set_conteudo($valores);
        $tabela->set_colunaSomatorio([1, 2, 3, 4]);
        $tabela->show();
    }

##############################################################################################################################################

    /**
     * Método get_numServidoresAtivosPodemAposentarIntegral
     * Informa o número de servidores que podem aposentar integralmente
     * 
     * @param string $parametroSexo sexo do servidor
     */
    public function get_numServidoresAtivosPodemAposentarIntegral($parametroSexo) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Monta o select
        $select = 'SELECT tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                   WHERE tbservidor.situacao = 1
                     AND idPerfil = 1
                     AND tbpessoa.sexo = "' . $parametroSexo . '"
                ORDER BY tbpessoa.dtNasc';

        $result = $pessoal->select($select);

        $contador = 0;

        # Percorre o banco para verificar se já pode aposentar
        foreach ($result as $lista) {

            # Pega a data de aposentadoria desse servidor
            $data = $this->get_dataAposentadoriaIntegral($lista[0]);

            # Verifica se a data colhida já passou
            if (jaPassou($data)) {
                $contador++;
            }
        }

        return $contador;
    }

##############################################################################################################################################

    /**
     * Método exibeServidoresAtivosPodemAposentarIntegral
     * Exibe os servidores que podem aposentar integralmente
     * 
     * @param string $parametroSexo sexo do servidor
     */
    public function exibeServidoresAtivosPodemAposentarIntegral($parametroSexo, $parametroLotacao = null, $parametroCargo = null) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Pega as idades de aposentadoria        
        if ($parametroSexo == "Feminino") {
            $idadeAposent = $intra->get_variavel("aposentadoria.integral.idade.feminino");
        } else {
            $idadeAposent = $intra->get_variavel("aposentadoria.integral.idade.masculino");
        }

        # Exibe a lista
        $select = "SELECT idFuncional,
                           tbpessoa.nome as servidor,
                           TIMESTAMPDIFF(YEAR, dtNasc, NOW()) AS idade,
                           idServidor
                      FROM tbservidor LEFT JOIN tbpessoa USING(idPessoa)
                                      LEFT JOIN tbhistlot USING (idServidor)
                                           JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                      LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                                      LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                     WHERE tbservidor.situacao = 1
                       AND idPerfil = 1
                       AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND tbpessoa.sexo = '{$parametroSexo}'
                       AND TIMESTAMPDIFF(YEAR, dtNasc, NOW()) >= {$idadeAposent}";

        # lotação
        if (!is_null($parametroLotacao)) {  // senão verifica o da classe
            if (is_numeric($parametroLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
            }
        }

        # cargo
        if (!is_null($parametroCargo)) {
            if (is_numeric($parametroCargo)) {
                $select .= " AND (tbcargo.idcargo = '{$parametroCargo}')";
            } else { # senão é nivel do cargo
                if ($parametroCargo == "Professor") {
                    $select .= " AND (tbcargo.idcargo = 128 OR  tbcargo.idcargo = 129)";
                } else {
                    $select .= " AND (tbtipocargo.cargo = '{$parametroCargo}')";
                }
            }
        }

        $select .= " ORDER BY idade";

        $result = $pessoal->select($select);
        $resultado = [];

        # Percorre o banco para verificar se já pode aposentar
        foreach ($result as $lista) {

            # Pega a data de aposentadoria desse servidor
            $data = $this->get_dataAposentadoriaIntegral($lista["idServidor"]);

            # Verifica se a data colhida já passou
            if (jaPassou($data)) {
                $resultado[] = [
                    $lista["idFuncional"],
                    $lista["idServidor"],
                    $lista["idServidor"],
                    $lista["idade"],
                    $this->get_tempoServicoTotal($lista["idServidor"]), // tempo total
                    dias_to_diasMesAno($this->get_tempoServicoUenf($lista["idServidor"])), // tempo no cargo
                    $data,
                    $lista["idServidor"]
                ];
            }
        }

        # Tabela com os valores de aposentadoria
        $tabela = new Tabela();
        $tabela->set_titulo("Servidores Ativos com Direito a Aposentadoria Integral - {$parametroSexo}");
        $tabela->set_label(['idFuncional', 'Servidor', 'Lotação', 'Idade', 'Tempo de Serviço', 'Tempo no Cargo', 'Data da Aposentadoria', 'Editar']);
        $tabela->set_align(['center', 'left']);
        $tabela->set_conteudo($resultado);
        $tabela->set_classe([null, "Pessoal", "Pessoal"]);
        $tabela->set_metodo([null, "get_nomeECargo", "get_lotacao"]);

        # Aposentadoria integral
        $servidorBtn = new Link(null, "?fase=editaIntegral&id=");
        $servidorBtn->set_imagem(PASTA_FIGURAS_GERAIS . 'bullet_edit.png', 20, 20);
        $servidorBtn->set_title("Vai para o cadastro do servidor");

        # Coloca os links na tabela			
        $tabela->set_link([null, null, null, null, null, null, null, $servidorBtn]);

        $tabela->show();
    }

##############################################################################################################################################

    /**
     * Método exibeServidoresAtivosPodemAposentarProporcional
     * Exibe os servidores que podem aposentar proporcional
     * 
     * @param string $parametroSexo sexo do servidor
     */
    public function exibeServidoresAtivosPodemAposentarProporcional($parametroSexo, $parametroLotacao = null, $parametroCargo = null) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Pega as idades de aposentadoria        
        if ($parametroSexo == "Feminino") {
            $idadeAposent = $intra->get_variavel("aposentadoria.proporcional.idade.feminino");
        } else {
            $idadeAposent = $intra->get_variavel("aposentadoria.proporcional.idade.masculino");
        }

        # Exibe a lista
        $select = "SELECT idFuncional,
                           tbpessoa.nome as servidor,
                           TIMESTAMPDIFF(YEAR, dtNasc, NOW()) AS idade,
                           idServidor
                      FROM tbservidor LEFT JOIN tbpessoa USING(idPessoa)
                                      LEFT JOIN tbhistlot USING (idServidor)
                                           JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                      LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                                      LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                     WHERE tbservidor.situacao = 1
                       AND idPerfil = 1
                       AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND tbpessoa.sexo = '{$parametroSexo}'
                       AND TIMESTAMPDIFF(YEAR, dtNasc, NOW()) >= {$idadeAposent}";

        # lotação
        if (!is_null($parametroLotacao)) {  // senão verifica o da classe
            if (is_numeric($parametroLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
            }
        }

        # cargo
        if (!is_null($parametroCargo)) {
            if (is_numeric($parametroCargo)) {
                $select .= " AND (tbcargo.idcargo = '{$parametroCargo}')";
            } else { # senão é nivel do cargo
                if ($parametroCargo == "Professor") {
                    $select .= " AND (tbcargo.idcargo = 128 OR  tbcargo.idcargo = 129)";
                } else {
                    $select .= " AND (tbtipocargo.cargo = '{$parametroCargo}')";
                }
            }
        }

        $select .= " ORDER BY idade";

        $result = $pessoal->select($select);
        $resultado = [];

        # Percorre o banco para verificar se já pode aposentar
        foreach ($result as $lista) {

            # Pega a data de aposentadoria desse servidor
            $data = $this->get_dataAposentadoriaProporcional($lista["idServidor"]);

            # Verifica se a data colhida já passou
            if (jaPassou($data)) {
                $resultado[] = [
                    $lista["idFuncional"],
                    $lista["idServidor"],
                    $lista["idServidor"],
                    $lista["idade"],
                    $this->get_tempoServicoTotal($lista["idServidor"]), // tempo total
                    dias_to_diasMesAno($this->get_tempoServicoUenf($lista["idServidor"])), // tempo no cargo
                    $data,
                    $lista["idServidor"]
                ];
            }
        }

        # Tabela com os valores de aposentadoria
        $tabela = new Tabela();
        $tabela->set_titulo("Servidores Ativos com Direito a Aposentadoria Proporcional - {$parametroSexo}");
        $tabela->set_label(['idFuncional', 'Servidor', 'Lotação', 'Idade', 'Tempo de Serviço', 'Tempo no Cargo', 'Data da Aposentadoria', 'Editar']);
        $tabela->set_align(['center', 'left']);
        $tabela->set_conteudo($resultado);
        $tabela->set_classe([null, "Pessoal", "Pessoal"]);
        $tabela->set_metodo([null, "get_nomeECargo", "get_lotacao"]);

        # Aposentadoria integral
        $servidorBtn = new Link(null, "?fase=editaProporcional&id=");
        $servidorBtn->set_imagem(PASTA_FIGURAS_GERAIS . 'bullet_edit.png', 20, 20);
        $servidorBtn->set_title("Vai para o cadastro do servidor");

        # Coloca os links na tabela			
        $tabela->set_link([null, null, null, null, null, null, null, $servidorBtn]);

        $tabela->show();
    }

##############################################################################################################################################

    /**
     * Método exibeServidoresAtivosPodemAposentarCompulsoria
     * Exibe os servidores que podem aposentar compulsoriamente
     * 
     * @param string $parametroSexo sexo do servidor
     */
    public function exibeServidoresAtivosPodemAposentarCompulsoria($parametroSexo, $parametroLotacao = null, $parametroCargo = null) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Pega as idades de aposentadoria 
        $idadeAposent = $intra->get_variavel("aposentadoria.compulsoria.idade");

        # Exibe a lista
        $select = "SELECT idFuncional,
                           tbpessoa.nome as servidor,
                           TIMESTAMPDIFF(YEAR, dtNasc, NOW()) AS idade,
                           idServidor
                      FROM tbservidor LEFT JOIN tbpessoa USING(idPessoa)
                                      LEFT JOIN tbhistlot USING (idServidor)
                                           JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                      LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                                      LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)
                     WHERE tbservidor.situacao = 1
                       AND idPerfil = 1
                       AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       AND tbpessoa.sexo = '{$parametroSexo}'
                       AND TIMESTAMPDIFF(YEAR, dtNasc, NOW()) >= {$idadeAposent}";

        # lotação
        if (!is_null($parametroLotacao)) {  // senão verifica o da classe
            if (is_numeric($parametroLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$parametroLotacao})";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$parametroLotacao}')";
            }
        }

        # cargo
        if (!is_null($parametroCargo)) {
            if (is_numeric($parametroCargo)) {
                $select .= " AND (tbcargo.idcargo = '{$parametroCargo}')";
            } else { # senão é nivel do cargo
                if ($parametroCargo == "Professor") {
                    $select .= " AND (tbcargo.idcargo = 128 OR  tbcargo.idcargo = 129)";
                } else {
                    $select .= " AND (tbtipocargo.cargo = '{$parametroCargo}')";
                }
            }
        }

        $select .= " ORDER BY idade";

        $result = $pessoal->select($select);
        $resultado = [];

        # Percorre o banco para verificar se já pode aposentar
        foreach ($result as $lista) {

            # Pega a data de aposentadoria desse servidor
            $data = $this->get_dataAposentadoriaCompulsoria($lista["idServidor"]);

            # Verifica se a data colhida já passou
            if (jaPassou($data)) {
                $resultado[] = [
                    $lista["idFuncional"],
                    $lista["servidor"],
                    $lista["idServidor"],
                    $lista["idServidor"],
                    $lista["idade"],
                    $data,
                    $lista["idServidor"]
                ];
            }
        }

        # Tabela com os valores de aposentadoria
        $tabela = new Tabela();
        $tabela->set_titulo("Servidores Ativos que Devem Ser Aposentados Compulsoriamente - {$parametroSexo}");
        $tabela->set_label(['idFuncional', 'Servidor', 'Cargo', 'Lotação', 'Idade', 'Data da Aposentadoria', 'Editar']);
        $tabela->set_align(['center', 'left', 'left', 'left']);
        $tabela->set_conteudo($resultado);
        $tabela->set_classe([null, null, "Pessoal", "Pessoal"]);
        $tabela->set_metodo([null, null, "get_cargo", "get_lotacao"]);

        # Aposentadoria integral
        $servidorBtn = new Link(null, "?fase=editaCompulsoria&id=");
        $servidorBtn->set_imagem(PASTA_FIGURAS_GERAIS . 'bullet_edit.png', 20, 20);
        $servidorBtn->set_title("Vai para o cadastro do servidor");

        # Coloca os links na tabela			
        $tabela->set_link([null, null, null, null, null, null, $servidorBtn]);

        $tabela->show();
    }

##############################################################################################################################################

    /**
     * Método get_numServidoresAtivosPodemAposentarProporcional
     * Informa o número de servidores que podem aposentar Proporcional
     * 
     * @param string $parametroSexo sexo do servidor
     */
    public function get_numServidoresAtivosPodemAposentarProporcional($parametroSexo) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Monta o select
        $select = 'SELECT tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                   WHERE tbservidor.situacao = 1
                     AND idPerfil = 1
                     AND tbpessoa.sexo = "' . $parametroSexo . '"
                ORDER BY tbpessoa.dtNasc';

        $result = $pessoal->select($select);

        $contador = 0;

        # Percorre o banco para verificar se já pode aposentar
        foreach ($result as $lista) {

            # Pega a data de aposentadoria desse servidor
            $data = $this->get_dataAposentadoriaProporcional($lista[0]);

            # Verifica se a data colhida já passou
            if (jaPassou($data)) {
                $contador++;
            }
        }

        return $contador;
    }

##############################################################################################################################################

    /**
     * Método get_numServidoresAtivosPodemAposentarCompulsoria
     * Informa o número de servidores que podem aposentar Compulsória
     * 
     * @param string $parametroSexo sexo do servidor
     */
    public function get_numServidoresAtivosPodemAposentarCompulsoria($parametroSexo) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Monta o select
        $select = 'SELECT tbservidor.idServidor
                    FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                   WHERE tbservidor.situacao = 1
                     AND idPerfil = 1
                     AND tbpessoa.sexo = "' . $parametroSexo . '"
                ORDER BY tbpessoa.dtNasc';

        $result = $pessoal->select($select);

        $contador = 0;

        # Percorre o banco para verificar se já pode aposentar
        foreach ($result as $lista) {

            # Pega a data de aposentadoria desse servidor
            $data = $this->get_dataAposentadoriaCompulsoria($lista[0]);

            # Verifica se a data colhida já passou
            if (jaPassou($data)) {
                $contador++;
            }
        }

        return $contador;
    }

##############################################################################################################################################

    /**
     * Método exibeTempo
     * Exibe tabela com o tempo de serviço do servidor
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function exibeTempo($idServidorPesquisado, $relatorio = false) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica a data de saída
        $dtSaida = $pessoal->get_dtSaida($idServidorPesquisado);      # Data de Saída de servidor inativo
        $dtHoje = date("Y-m-d");                                      # Data de hoje
        $dtFinal = null;

        # Analisa a data
        if (!vazio($dtSaida)) {           // Se tem saída é a saída
            $dtFinal = date_to_bd($dtSaida);
        } else {                          // Não tem saída então é hoje
            $dtFinal = $dtHoje;
        }

        # Gênero do servidor
        $sexo = $pessoal->get_sexo($idServidorPesquisado);

        # Tempo de Serviço
        $uenf = $pessoal->get_tempoServicoUenf($idServidorPesquisado, $dtFinal);
        $publica = $pessoal->get_totalAverbadoPublico($idServidorPesquisado);
        $privada = $pessoal->get_totalAverbadoPrivado($idServidorPesquisado);
        $totalTempo = $uenf + $publica + $privada;

        # Título
        if ($relatorio) {
            p("Tempo de Serviço", "f16", "center");
        } else {
            titulo("Tempo de Serviço");
            br();
        }

        # Verifica se tem sobreposição
        $selectSobreposicao = 'SELECT dtInicial, dtFinal, idAverbacao FROM tbaverbacao WHERE idServidor = ' . $idServidorPesquisado . ' ORDER BY dtInicial';
        $resultSobreposicao = $pessoal->select($selectSobreposicao);

        # Acrescenta o tempo de UENF
        $dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidorPesquisado));
        $resultSobreposicao[] = array($dtAdmissao, $dtFinal, null);

        # Inicia a variável que informa se tem sobreposicao
        $sobreposicao = false;

        # Inicia o array que guarda os períodos problemáticos
        $idsProblemáticos[] = null;

        # Percorre os registros
        foreach ($resultSobreposicao as $periodo) {
            $dtInicial1 = date_to_php($periodo[0]);
            $dtFinal1 = date_to_php($periodo[1]);
            $idAverbado1 = $periodo[2];

            # Percorre a mesma listagem novamente
            foreach ($resultSobreposicao as $periodoVerificado) {

                $dtInicial2 = date_to_php($periodoVerificado[0]);
                $dtFinal2 = date_to_php($periodoVerificado[1]);
                $idAverbado2 = $periodoVerificado[2];

                # Evita que seja comparado com ele mesmo
                if ($idAverbado1 <> $idAverbado2) {
                    if (verificaSobreposicao($dtInicial1, $dtFinal1, $dtInicial2, $dtFinal2)) {
                        $sobreposicao = true;
                        $idsProblemáticos[] = $idAverbado1;
                        $idsProblemáticos[] = $idAverbado2;
                    }
                }
            }
        }

        if ($sobreposicao) {

            $painel = new Callout("alert", "center");
            $painel->abre();
            echo "Atenção - Períodos com Sobreposição de Dias !!!";
            p("Verifique se não há dias sobrepostos entre os períodos averbados<br/>ou se algum período averbado ultrapassa a data de admissão na UENF: " . date_to_php($dtAdmissao), "center", "f11");
            $painel->fecha();
        }

        ###
        # Limita a tela
        $grid = new Grid();

        # Tempo público e privado
        $grid->abreColuna(4);

        # Monta o array
        $dados1 = array(
            array("UENF ", $uenf),
            array("Empresa Pública", $publica),
            array("Empresa Privada", $privada),
            array("Total", $totalTempo . " dias<br/>(" . dias_to_diasMesAno($totalTempo) . ")")
        );

        if ($relatorio) {
            $relatorio = new Relatorio();
            $relatorio->set_subtitulo("Tempo Averbado");
            $relatorio->set_label(array("Descrição", "Dias"));
            $relatorio->set_align(array('left'));
            $relatorio->set_conteudo($dados1);
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_exibeSomatorioGeral(false);
            $relatorio->set_botaoVoltar(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->show();
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo('Tempo Averbado');
            $tabela->set_conteudo($dados1);
            $tabela->set_label(array("Descrição", "Dias"));
            $tabela->set_align(array("left", "center"));
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                    'valor' => "Total",
                    'operador' => '=',
                    'id' => 'totalTempo')));
            $tabela->show();
        }

        $grid->fechaColuna();

        #############################3
        # Ocorrências
        $grid->abreColuna(4);

        # Monta o select
        $reducao = "SELECT tbtipolicenca.nome as tipo,
                           SUM(numDias) as dias
                      FROM tblicenca JOIN tbtipolicenca USING(idTpLicenca)
                     WHERE idServidor = $idServidorPesquisado
                       AND tbtipolicenca.tempoServico IS true
                  GROUP BY tbtipolicenca.nome";

        $dados2 = $pessoal->select($reducao);

        # Somatório
        $totalOcorrencias = array_sum(array_column($dados2, 'dias'));

        # Adiciona na tabela
        if ($totalOcorrencias == 0) {
            array_push($dados2, array("Sem Ocorrências", "---"));
        } else {
            array_push($dados2, array("Total", $totalOcorrencias));
        }

        if ($relatorio) {
            $relatorio = new Relatorio();
            $relatorio->set_subtitulo("Ocorrências");
            $relatorio->set_label(array("Descrição", "Dias"));
            $relatorio->set_align(array('left'));
            $relatorio->set_conteudo($dados1);
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_exibeSomatorioGeral(false);
            $relatorio->set_botaoVoltar(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->show();
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo('Ocorrências');
            $tabela->set_conteudo($dados2);
            $tabela->set_label(array("Descrição", "Dias"));
            $tabela->set_align(array("left"));
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                    'valor' => "Total",
                    'operador' => '=',
                    'id' => 'totalTempo')));
            $tabela->show();
        }

        $grid->fechaColuna();

        #############################3
        # Total do Tempo
        $grid->abreColuna(4);

        # Calcula o tempo de serviço geral
        $totalTempoGeral = $totalTempo - $totalOcorrencias;

        # Monta o array
        $dados3 = array(
            array("Tempo de Serviço ", $totalTempo),
            array("Ocorrências", "($totalOcorrencias)"),
            array("Total", $totalTempoGeral)
        );

        if ($relatorio) {
            $relatorio = new Relatorio();
            $relatorio->set_subtitulo('Resumo Geral');
            $relatorio->set_label(array("Descrição", "Dias"));
            $relatorio->set_align(array('left'));
            $relatorio->set_conteudo($dados1);
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_exibeSomatorioGeral(false);
            $relatorio->set_botaoVoltar(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->show();
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo('Resumo Geral');
            $tabela->set_conteudo($dados3);
            $tabela->set_label(array("Descrição", "Dias"));
            $tabela->set_align(array("left", "center"));
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                    'valor' => "Total",
                    'operador' => '=',
                    'id' => 'totalTempo'),
                array('coluna' => 0,
                    'valor' => "Ocorrências",
                    'operador' => '=',
                    'id' => 'ocorrencia'),
                array('coluna' => 0,
                    'valor' => "Dias Sobrando",
                    'operador' => '=',
                    'id' => 'diasSobrando'),
                array('coluna' => 0,
                    'valor' => "Dias Faltando",
                    'operador' => '=',
                    'id' => 'diasFaltando')));
            $tabela->show();
        }

        $grid->fechaColuna();
        $grid->fechaGrid();

        #$painel->fecha();
    }

##############################################################################################################################################

    /**
     * Método exibePrevisao
     * Exibe análise da aposentadoria geral
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function exibePrevisao($idServidorPesquisado, $relatorio = false) {

        # Título
        if ($relatorio) {
            p("Previsão de Aposentadoria", "f16", "center");
        } else {
            titulo("Previsão de Aposentadoria");
            br();
        }

        $grid1 = new Grid();
        $grid1->abreColuna(4);

        # Aposentadoria Integral
        $this->exibePrevisaoIntegral($idServidorPesquisado, $relatorio);

        $grid1->fechaColuna();

        #############################################

        $grid1->abreColuna(4);

        # Aposentadoria Proporcional
        $this->exibePrevisaoProporcional($idServidorPesquisado, $relatorio);

        $grid1->fechaColuna();

        #############################################

        $grid1->abreColuna(4);

        # Aposentadoria Compulsória
        $this->exibePrevisaoCompulsoria($idServidorPesquisado, $relatorio);

        $grid1->fechaColuna();
        $grid1->fechaGrid();
    }

##############################################################################################################################################    

    /**
     * Método exibePrevisaoIntegral
     * Exibe análise da aposentadoria integral do servidor
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function exibePrevisaoIntegral($idServidorPesquisado, $relatorio = false) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Verifica a data de saída
        $dtSaida = $pessoal->get_dtSaida($idServidorPesquisado);      # Data de Saída de servidor inativo
        $dtHoje = date("Y-m-d");                                      # Data de hoje
        $dtFinal = null;

        # Analisa a data
        if (!vazio($dtSaida)) {           // Se tem saída é a saída
            $dtFinal = date_to_bd($dtSaida);
        } else {                          // Não tem saída então é hoje
            $dtFinal = $dtHoje;
        }

        # Gênero do servidor
        $sexo = $pessoal->get_sexo($idServidorPesquisado);

        # Idade do Servidor
        $idade = $pessoal->get_idade($idServidorPesquisado);

        # Idade da Aposentadoria
        switch ($sexo) {
            case "Masculino" :
                $anosAposentadoria = $intra->get_variavel("aposentadoria.integral.idade.masculino");
                $diasAposentadoriaIntegral = $intra->get_variavel("aposentadoria.integral.tempo.masculino");
                $tempoCargoAposentadoriaIntegral = $intra->get_variavel("aposentadoria.integral.tempo.cargo.masculino");
                break;
            case "Feminino" :
                $anosAposentadoria = $intra->get_variavel("aposentadoria.integral.idade.feminino");
                $diasAposentadoriaIntegral = $intra->get_variavel("aposentadoria.integral.tempo.feminino");
                $tempoCargoAposentadoriaIntegral = $intra->get_variavel("aposentadoria.integral.tempo.cargo.feminino");
                break;
        }

        # Tempo de Serviço
        $uenf = $this->get_tempoServicoUenf($idServidorPesquisado);
        $publica = $this->get_tempoAverbadoPublico($idServidorPesquisado);
        $privada = $this->get_tempoAverbadoPrivado($idServidorPesquisado);
        $ocorrencia = $this->get_tempoOcorrencias($idServidorPesquisado);
        $totalTempo = ($uenf + $publica + $privada) - $ocorrencia;

        # Aposentadoria Integral
        $dtAposentadoriaIntegralIdade = $this->get_dataAposentadoriaIntegralIdade($idServidorPesquisado);
        $dtAposentadoriaIntegralTempo = $this->get_dataAposentadoriaIntegralTempo($idServidorPesquisado);
        $dtAposentadoriaIntegral = $this->get_dataAposentadoriaIntegral($idServidorPesquisado);
        $faltam = $diasAposentadoriaIntegral - $totalTempo;

        #$painel = new Callout();
        #$painel->abre();
        # Aposentadoria Integral
        if (jaPassou($dtAposentadoriaIntegral)) {
            callout("Desde $dtAposentadoriaIntegral o servidor já pode solicitar Aposentadoria Integral!", "success");
        } else {
            callout("Aposentadoria Integral somente em: $dtAposentadoriaIntegral.", "warning");
        }

        # Monta o array
        $dados1 = array(
            array("Idade", $anosAposentadoria, $idade),
            array("Tempo de Serviço", $diasAposentadoriaIntegral, $totalTempo),
            array("Tempo no cargo", $tempoCargoAposentadoriaIntegral, dias_to_diasMesAno($uenf))
        );

        if ($relatorio) {
            $relatorio = new Relatorio();
            $relatorio->set_subtitulo("Aposentadoria Integral");
            $relatorio->set_label(array("Descrição", "Regra", "Servidor"));
            $relatorio->set_align(array('left'));
            $relatorio->set_conteudo($dados1);
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_exibeSomatorioGeral(false);
            $relatorio->set_botaoVoltar(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->show();
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo('Aposentadoria Integral');
            $tabela->set_conteudo($dados1);
            $tabela->set_label(array("Descrição", "Regra", "Servidor"));
            $tabela->set_align(array("left"));
            $tabela->set_totalRegistro(false);
            $tabela->show();
        }

        hr("procedimento");

        # Análise por idade
        if ($anosAposentadoria > $idade) {
            p("O servidor ainda não alcançou os <b>$anosAposentadoria</b> anos de idade de para solicitar aposentadoria integral. Somente em $dtAposentadoriaIntegralIdade.", "f14");
        } else {
            p("O servidor já alcançou a idade para solicitar aposentadoria integral.", "f14");
        }

        hr("procedimento");

        # Análise por Tempo de Serviço
        if ($diasAposentadoriaIntegral > $totalTempo) {
            p("Ainda faltam <b>$faltam</b> dias para o servidor alcançar os <b>$diasAposentadoriaIntegral</b> dias de serviço necessários para solicitar a aposentadoria integral. Somente em $dtAposentadoriaIntegralTempo.", "f14");
        } else {
            p("O servidor já alcançou os <b>$diasAposentadoriaIntegral</b> dias de tempo de serviço para solicitar aposentadoria integral.", "f14");
        }

        hr("procedimento");

        # Análise por Tempo no cargo
        if ($tempoCargoAposentadoriaIntegral > dias_to_Ano($uenf)) {
            p("O servidor ainda não possui os {$tempoCargoAposentadoriaIntegral} anos no mesmo cargo para solicitar a aposentadoria", "f14");
        } else {
            p("O servidor já alcançou os <b>{$tempoCargoAposentadoriaIntegral}</b> anos no mesmo cargo para solicitar a aposentadoria integral.", "f14");
        }

        ### sobreposição
        # Verifica se tem sobreposição
        $selectSobreposicao = 'SELECT dtInicial, dtFinal, idAverbacao FROM tbaverbacao WHERE idServidor = ' . $idServidorPesquisado . ' ORDER BY dtInicial';
        $resultSobreposicao = $pessoal->select($selectSobreposicao);

        # Acrescenta o tempo de UENF
        $dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidorPesquisado));
        $resultSobreposicao[] = array($dtAdmissao, $dtFinal, null);

        # Inicia a variável que informa se tem sobreposicao
        $sobreposicao = false;

        # Inicia o array que guarda os períodos problemáticos
        $idsProblemáticos[] = null;

        # Percorre os registros
        foreach ($resultSobreposicao as $periodo) {
            $dtInicial1 = date_to_php($periodo[0]);
            $dtFinal1 = date_to_php($periodo[1]);
            $idAverbado1 = $periodo[2];

            # Percorre a mesma listagem novamente
            foreach ($resultSobreposicao as $periodoVerificado) {

                $dtInicial2 = date_to_php($periodoVerificado[0]);
                $dtFinal2 = date_to_php($periodoVerificado[1]);
                $idAverbado2 = $periodoVerificado[2];

                # Evita que seja comparado com ele mesmo
                if ($idAverbado1 <> $idAverbado2) {
                    if (verificaSobreposicao($dtInicial1, $dtFinal1, $dtInicial2, $dtFinal2)) {
                        $sobreposicao = true;
                        $idsProblemáticos[] = $idAverbado1;
                        $idsProblemáticos[] = $idAverbado2;
                    }
                }
            }
        }

        if ($sobreposicao) {

            $painel = new Callout("alert", "center");
            $painel->abre();
            echo "Atenção - Períodos com Sobreposição de Dias !!!";
            p("Verifique se não há dias sobrepostos entre os períodos averbados<br/>ou se algum período averbado ultrapassa a data de admissão na UENF: " . date_to_php($dtAdmissao), "center", "f11");
            $painel->fecha();
        }

        #$painel->fecha();
    }

    ##############################################################################################################################################

    /**
     * Método exibePrevisaoProporcional
     * Exibe análise da aposentadoria proporcional do servidor
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function exibePrevisaoProporcional($idServidorPesquisado, $relatorio = false) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Gênero do servidor
        $sexo = $pessoal->get_sexo($idServidorPesquisado);

        # Idade do Servidor
        $idade = $pessoal->get_idade($idServidorPesquisado);

        # Aposentadoria Proporcional
        $dtAposentadoriaProporcional = $this->get_dataAposentadoriaProporcional($idServidorPesquisado);
        $dtAposentadoriaProporcionalIdade = $this->get_dataAposentadoriaProporcionalIdade($idServidorPesquisado);
        $dtAposentadoriaProporcionalTempo = $this->get_dataAposentadoriaProporcionalTempo($idServidorPesquisado);

        # Idade da Aposentadoria
        switch ($sexo) {
            case "Masculino" :
                $regraIdadeProporcional = $intra->get_variavel("aposentadoria.proporcional.idade.masculino");
                $regraTempoProporcional = $intra->get_variavel("aposentadoria.proporcional.tempo.masculino");
                $tempoCargoAposentadoriaProporcional = $intra->get_variavel("aposentadoria.proporcional.tempo.cargo.masculino");
                break;
            case "Feminino" :
                $regraIdadeProporcional = $intra->get_variavel("aposentadoria.proporcional.idade.feminino");
                $regraTempoProporcional = $intra->get_variavel("aposentadoria.proporcional.tempo.feminino");
                $tempoCargoAposentadoriaProporcional = $intra->get_variavel("aposentadoria.proporcional.tempo.cargo.feminino");
                break;
        }

        # Tempo de Serviço
        $uenf = $this->get_tempoServicoUenf($idServidorPesquisado);
        $publica = $this->get_tempoAverbadoPublico($idServidorPesquisado);
        $ocorrencia = $this->get_tempoOcorrencias($idServidorPesquisado);
        $totalPublico = ($uenf + $publica) - $ocorrencia;

        # Aposentadoria Proporcional
        if (jaPassou($dtAposentadoriaProporcional)) {
            callout("Desde $dtAposentadoriaProporcional o servidor já pode solicitar Aposentadoria Proporcional!", "success");
        } else {
            callout("Aposentadoria Proporcional somente em: $dtAposentadoriaProporcional.", "warning");
        }

        # Monta o array
        $dados1 = array(
            array("Idade", $regraIdadeProporcional, $idade),
            array("Tempo de Serviço Público", $regraTempoProporcional, $totalPublico),
            array("Tempo no cargo", $tempoCargoAposentadoriaProporcional, dias_to_diasMesAno($uenf))
        );

        if ($relatorio) {
            $relatorio = new Relatorio();
            $relatorio->set_subtitulo("Aposentadoria Proporcional");
            $relatorio->set_label(array("Descrição", "Regra", "Servidor"));
            $relatorio->set_align(array('left'));
            $relatorio->set_conteudo($dados1);
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_exibeSomatorioGeral(false);
            $relatorio->set_botaoVoltar(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->show();
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo('Aposentadoria Proporcional');
            $tabela->set_conteudo($dados1);
            $tabela->set_label(array("Descrição", "Regra", "Servidor"));
            $tabela->set_align(array("left"));
            $tabela->set_totalRegistro(false);
            $tabela->show();
        }

        hr("procedimento");

        # Dias que faltam
        $faltamProporcional = $regraTempoProporcional - $totalPublico;

        # Análise por idade
        if ($regraIdadeProporcional > $idade) {
            p("O servidor ainda não alcançou os <b>$regraIdadeProporcional</b> anos de idade de para solicitar aposentadoria proporcional. Somente em $dtAposentadoriaProporcionalIdade.", "f14");
        } else {
            p("O servidor já alcançou a idade para solicitar aposentadoria proporcional.", "f14");
        }

        hr("procedimento");

        # Análise por Tempo de Serviço
        if ($regraTempoProporcional > $totalPublico) {
            p("Ainda faltam <b>$faltamProporcional</b> dias para o servidor alcançar os <b>$regraTempoProporcional</b> dias de serviço necessários para solicitar a aposentadoria proporcional. Somente em $dtAposentadoriaProporcionalTempo.", "f14");
        } else {
            p("O servidor já alcançou os <b>$regraTempoProporcional</b> dias de tempo serviço público para solicitar aposentadoria proporcional.", "f14");
        }

        hr("procedimento");

        # Análise por Tempo no cargo
        if ($tempoCargoAposentadoriaProporcional > dias_to_Ano($uenf)) {
            p("O servidor ainda não possui os {$tempoCargoAposentadoriaProporcional} anos no mesmo cargo para solicitar a aposentadoria", "f14");
        } else {
            p("O servidor já alcançou os <b>{$tempoCargoAposentadoriaProporcional}</b> anos no mesmo cargo para solicitar a aposentadoria integral.", "f14");
        }
    }

    ##############################################################################################################################################

    /**
     * Método exibePrevisaoCompulsoria
     * Exibe análise da aposentadoria compulsória
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function exibePrevisaoCompulsoria($idServidorPesquisado, $relatorio = false) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $intra = new Intra();

        # Idade do Servidor
        $idade = $pessoal->get_idade($idServidorPesquisado);

        # Aposentadoria Compulsória
        $dtAposentadoriaCompulsoria = $this->get_dataAposentadoriaCompulsoria($idServidorPesquisado);
        $idadeAposentadoriaCompulsoria = $intra->get_variavel("aposentadoria.compulsoria.idade");

        # Aposentadoria Compulsória
        if (jaPassou($dtAposentadoriaCompulsoria)) {
            callout("Desde $dtAposentadoriaCompulsoria o servidor terá que se aposentar compulsoriamente!", "success");
        } else {
            callout("Aposentadoria Compulsória somente em: $dtAposentadoriaCompulsoria.", "warning");
        }

        # Monta o array
        $dados1 = array(
            array("Idade", $idadeAposentadoriaCompulsoria, $idade));

        if ($relatorio) {
            $relatorio = new Relatorio();
            $relatorio->set_subtitulo("Aposentadoria Compulsória");
            $relatorio->set_label(array("Descrição", "Regra", "Servidor"));
            $relatorio->set_align(array('left'));
            $relatorio->set_conteudo($dados1);
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_exibeSomatorioGeral(false);
            $relatorio->set_botaoVoltar(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->show();
        } else {
            $tabela = new Tabela();
            $tabela->set_titulo('Aposentadoria Compulsória');
            $tabela->set_conteudo($dados1);
            $tabela->set_label(array("Descrição", "Regra", "Servidor"));
            $tabela->set_align(array("left"));
            $tabela->set_totalRegistro(false);
            $tabela->show();
        }

        br();
        hr("procedimento");

        # Análise por idade
        if (75 > $idade) {
            p("O servidor ainda não alcançou os <b>$idadeAposentadoriaCompulsoria</b> anos de idade de para a aposentadoria compulsória. Somente em $dtAposentadoriaCompulsoria.", "f14");
        } else {
            p("O servidor já alcançou a idade para a aposentadoria compulsória.", "f14");
        }
    }

    ##############################################################################################################################################

    /**
     * Método exibeMenu
     * Exibe menu da área de aposentadoria
     * 
     * @param string $itemBold o item do menu para colocar o bold no menu
     */
    public function exibeMenu($itemBold = null) {

        # Pega as idades de aposentadoria
        $intra = new Intra();
        $idadeAposentMasculino = $intra->get_variavel("aposentadoria.integral.idade.masculino");
        $idadeAposentFeminino = $intra->get_variavel("aposentadoria.integral.idade.feminino");

        $menu = new Menu("menuAposentadoria", $itemBold);

        $menu->add_item("titulo", "Servidores Aposentados");

        $menu->add_item("link", "Aposentados por Ano", "areaAposentadoria.php?fase=porAno", "Servidores Aposentados por Ano de Aposentadoria");
        $menu->add_item("link", "Aposentados por Tipo", "areaAposentadoria.php?fase=motivo", "Servidores Aposentados por Tipo de Aposentadoria");
        $menu->add_item("link", "Estatística", "areaAposentadoria.php?fase=anoEstatistica", "Estatística dos Servidores Aposentados");

        $menu->add_item("titulo", "Servidores Ativos");

        $menu->add_item("titulo1", "Previsão");
        $menu->add_item("link", "Masculino Geral", "areaAposentadoria.php?fase=previsaoM", "Previsão de Aposentadoria de Servidores Ativos do Sexo Masculino");
        $menu->add_item("link", "Feminino Geral", "areaAposentadoria.php?fase=previsaoF", "Previsão de Aposentadoria de Servidores Ativos do Sexo Feminino");
        $menu->add_item("link", "Compulsória Por Ano", "areaAposentadoria.php?fase=compulsoria", "Previsão de Aposentadoria Compulsória");

        $menu->add_item("titulo1", "Podem Aposentar");
        $menu->add_item("link", "Integral", "areaAposentadoria.php?fase=aguardaIntegral&parametroSexo=Feminino", "Somatório de Servidores Ativos que Podem se Aposentar");
        $menu->add_item("link", "Proporcional", "areaAposentadoria.php?fase=aguardaProporcional", "Somatório de Servidores Ativos que Podem se Aposentar");
        $menu->add_item("link", "Compulsória", "areaAposentadoria.php?fase=aguardaCompulsoria", "Somatório de Servidores Ativos que Podem se Aposentar");
        $menu->add_item("link", "Resumo", "areaAposentadoria.php?fase=somatorio", "Somatório de Servidores Ativos que Podem se Aposentar");

        $menu->add_item("titulo", "Configurações");
        $menu->add_item("link", "Regras", "areaAposentadoria.php?fase=regras", "Regras de Aposentadoria");
        $menu->add_item("link", "Aposentadoria Integral", "areaAposentadoria.php?fase=configuracaoIntegral", "Configuração da aposentadoria integral");
        $menu->add_item("link", "Aposentadoria Proporcional", "areaAposentadoria.php?fase=configuracaoProporcional", "Configuração da aposentadoria proporcional");
        $menu->add_item("link", "Aposentadoria Compulsória", "areaAposentadoria.php?fase=configuracaoCompulsoria", "Configuração da aposentadoria compulsória");

        $menu->show();
    }

    ##############################################################################################################################################

    /**
     * Método get_ultimoAnoAposentadoria
     * informa ultimo ano de uma aposentadoria no banco de dados
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_ultimoAnoAposentadoria() {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        $select = 'SELECT YEAR(tbservidor.dtDemissao)
                         FROM tbservidor 
                        WHERE situacao = 2
                          AND (tbservidor.idPerfil = 1 OR tbservidor.idPerfil = 4)
                     ORDER BY 1 desc
                     LIMIT 1';

        $ano = $pessoal->select($select, false);

        if (empty($ano[0])) {
            return null;
        } else {
            return $ano[0];
        }
    }

##############################################################################################################################################
}
