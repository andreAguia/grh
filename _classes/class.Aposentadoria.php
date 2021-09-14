<?php

class Aposentadoria {
    /**
     * Abriga as várias rotina referentes a aposentadoria do servidor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */

    /**
     * Método exibeMenu
     * Exibe menu da área de aposentadoria
     * 
     * @param string $itemBold o item do menu para colocar o bold no menu
     */
    ##################################################

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

        $menu->add_item("titulo", "Regras Permanentes");
        $menu->add_item("link", "Por Idade e Contribuição", "areaAposentadoria.php?fase=porIdadeContribuicao", "Aposentadoria voluntária por idade e tempo de contribuição");
        $menu->add_item("link", "Por Idade", "areaAposentadoria.php?fase=porIdade", "Aposentadoria voluntária por idade");
        $menu->add_item("link", "Compulsória", "areaAposentadoria.php?fase=compulsoriaGeral", "Previsão de aposentadoria compulsória");
        $menu->add_item("link", "Compulsória por Ano", "areaAposentadoria.php?fase=compulsoria", "Previsão de aposentadoria compulsória por ano");
        #$menu->add_item("link", "Configuração Compulsória", "areaAposentadoria.php?fase=configuracaoCompulsoria", "Configuração");
        
        $menu->add_item("titulo", "Regras de Transição");
        $menu->add_item("link", "EC nº 41/2003", "areaAposentadoria.php?fase=transicao1", "Regras de transição - EC nº 41/2003");
        $menu->add_item("link", "EC nº 47/2005", "areaAposentadoria.php?fase=transicao2", "Regras de transição - EC nº 47/2005");
        $menu->show();
    }

    ############################################################################

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

    ############################################################################ 

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

    ############################################################################

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

    ############################################################################

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
     * Método get_dtIngresso
     * informa a data de ingresso
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_dtIngresso($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Pega os dados do servidor
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

        # Pega os dados
        $select = "SELECT dtInicial,
                          dtFinal
                     FROM tbaverbacao
                    WHERE empresaTipo = 1 AND idServidor = {$idServidor}
                 ORDER BY dtInicial DESC";

        $result = $pessoal->select($select);
        $dtReferencia = $dtAdmissao;

        # Percorre os registros
        foreach ($result as $periodo) {
            $dtInicial = date_to_php($periodo[0]);
            $dtFinal = date_to_php($periodo[1]);

            # Verifica se é initerrupto
            if (($dtFinal == $dtReferencia) OR ($dtFinal == addDias($dtReferencia, -1, false))) {
                $dtReferencia = $dtInicial;
            } else {
                return $dtReferencia;
            }
        }
        return $dtReferencia;
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
     * Método get_tempoPublicoIninterrupto
     * informa em dias o tempo publico ininterrupto
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoPublicoIninterrupto($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Pega a data de Ingresso
        $dtIngresso = $this->get_dtIngresso($idServidor);

        # Pega os dados
        $select = "SELECT dtInicial,
                          dias
                     FROM tbaverbacao
                    WHERE empresaTipo = 1 AND idServidor = {$idServidor}
                 ORDER BY dtInicial DESC";

        $result = $pessoal->select($select);
        $totalDias = $this->get_tempoServicoUenf($idServidor);

        # Percorre o arquivo de averbação para pegar os dias digitados (e não calculados)
        foreach ($result as $periodo) {

            # Se a data inicial do tempo averbado for igual ou maior que a data
            # de ingresso então acrescenta os dias
            if (strtotime(date_to_bd($dtIngresso)) <= strtotime($periodo[0])) {
                $totalDias += $periodo[1];
            }
        }
        
        return $totalDias;
    }

##############################################################################################################################################
}
