<?php

/**
 * Sistema GRH
 * 
 * Ficha Cadastral
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("../grhSistema/_config.php");

# Verifica qual será o id
if (empty($idServidorPesquisado)) {
    alert("É necessário informar o id do Servidor.");
}

# Pega os parâmetros do relatório
$postContatos = post('contatos');
$postDependentes = post('dependentes');
$postFormacao = post('formacao');
$postLotacao = post('lotacao');
$postTrienio = post('trienio');
$postFerias = post('ferias');
$postLicenca = post('licenca');
$postCargo = post('cargo');
$postProgressao = post('progressao');
$postGratificacao = post('gratificacao');
$postAverbacao = post('averbacao');
$postDiaria = post('diaria');
$postAbono = post('abono');
$postDireito = post('direito');
$postPenalidade = post('penalidade');
$postElogio = post('elogio');
$postAcumulacao = post('acumulacao');
$postDadosUsuario = post('dadosUsuario');

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados    
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);

    /*
     * Dados Principais
     */

    $select = 'SELECT tbservidor.idFuncional,
                      tbservidor.matricula,
                      tbpessoa.nome,
                      tbservidor.idServidor,
                      tbperfil.nome,
                      tbsituacao.situacao 
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                 LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                                 LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                WHERE tbservidor.idServidor = ' . $idServidorPesquisado;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    $relatorio->set_titulo('Ficha Cadastral');
    $relatorio->set_label(array('IdFuncional', 'Matrícula', 'Nome', 'Lotaçao', 'Perfil', 'Situação'));
    #$relatorio->set_width(array(15,10,40,15,20));
    $relatorio->set_funcao(array(null, "dv"));
    $relatorio->set_classe(array(null, null, null, "pessoal"));
    $relatorio->set_metodo(array(null, null, null, "get_lotacao"));
    $relatorio->set_align(array('center'));
    $relatorio->set_conteudo($result);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_linhaNomeColuna(false);
    $relatorio->set_brHr(0);
    $relatorio->set_formCampos(array(
        array('nome' => 'contatos',
            'label' => 'Contatos',
            'tipo' => 'simnao',
            'valor' => $postContatos,
            'size' => 5,
            'title' => 'Exibe os Contatos do Servidor (Telefones, Emails, etc)',
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1),
        array('nome' => 'formacao',
            'label' => 'Formação',
            'tipo' => 'simnao',
            'size' => 5,
            'title' => 'Exibe a Área de Foemação Educacional do servidor',
            'valor' => $postFormacao,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1),
        array('nome' => 'lotacao',
            'label' => 'Lotação',
            'tipo' => 'simnao',
            'size' => 5,
            'title' => 'Exibe o Histórico de Lotação do Servidor',
            'valor' => $postLotacao,
            'col' => 3,
            'onChange' => 'formPadrao.submit();',
            'linha' => 1),
        array('nome' => 'dependentes',
            'label' => 'Dependentes',
            'tipo' => 'simnao',
            'size' => 5,
            'title' => 'Exibe os Dependentes do Servidor',
            'valor' => $postDependentes,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 1),
        array('nome' => 'cargo',
            'label' => 'Cargos em Comissão',
            'tipo' => 'simnao',
            'size' => 5,
            'title' => 'Exibe o Histórico de Cargos em Comissão',
            'valor' => $postCargo,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 2),
        array('nome' => 'trienio',
            'label' => 'Triênio',
            'tipo' => 'simnao',
            'size' => 5,
            'title' => 'Exibe o Histórico de Triênio',
            'valor' => $postTrienio,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 2),
        array('nome' => 'ferias',
            'label' => 'Férias',
            'tipo' => 'simnao',
            'size' => 1,
            'title' => 'Exibe o Histórico de Férias',
            'valor' => $postFerias,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 2),
        array('nome' => 'licenca',
            'label' => 'Licenças',
            'tipo' => 'simnao',
            'size' => 1,
            'title' => 'Exibe o Histórico de Licença',
            'valor' => $postLicenca,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 2),
        array('nome' => 'progressao',
            'label' => 'Progressões',
            'tipo' => 'simnao',
            'size' => 1,
            'title' => 'Exibe o Histórico de Progressões',
            'valor' => $postProgressao,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 3),
        array('nome' => 'gratificacao',
            'label' => 'Gratificações',
            'tipo' => 'simnao',
            'size' => 1,
            'title' => 'Exibe o Histórico de Gratificação Especial',
            'valor' => $postGratificacao,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 3),
        array('nome' => 'diaria',
            'label' => 'Diária',
            'tipo' => 'simnao',
            'size' => 1,
            'title' => 'Exibe o Histórico de Diária',
            'valor' => $postDiaria,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 3),
        array('nome' => 'averbacao',
            'label' => 'Tempo de Serviço',
            'tipo' => 'simnao',
            'size' => 1,
            'title' => 'Exibe o Tempo de Serviço Averbado e Cadastrado no SAPE',
            'valor' => $postAverbacao,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 3),
        array('nome' => 'abono',
            'label' => 'Abono Permanência',
            'tipo' => 'simnao',
            'size' => 1,
            'title' => 'Exibe as informaçoes do abono Permanência',
            'valor' => $postAbono,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 3),
        array('nome' => 'direito',
            'label' => 'Direito Pessoal',
            'tipo' => 'simnao',
            'size' => 1,
            'title' => 'Exibe Informaçoes do direito pessoal do servidor',
            'valor' => $postDireito,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 3),
        array('nome' => 'penalidade',
            'label' => 'Penalidades',
            'tipo' => 'simnao',
            'size' => 1,
            'title' => 'Exibe se o servidor teve alguma penalidade',
            'valor' => $postPenalidade,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 3),
        array('nome' => 'elogio',
            'label' => 'Elogios',
            'tipo' => 'simnao',
            'size' => 1,
            'title' => 'Exibe se o servidor teve algum elogio',
            'valor' => $postElogio,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 3),
        array('nome' => 'acumulacao',
            'label' => 'Acumulação',
            'tipo' => 'simnao',
            'size' => 1,
            'title' => 'Exibe se o servidor teve Acumulação de Cargos',
            'valor' => $postAcumulacao,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 4),
        array('nome' => 'dadosUsuario',
            'label' => 'Dados Usuário',
            'tipo' => 'simnao',
            'size' => 1,
            'title' => 'Exibe ou não os dados do usuário que emitiu a ficha',
            'valor' => $postDadosUsuario,
            'onChange' => 'formPadrao.submit();',
            'col' => 3,
            'linha' => 4)
    ));

    $relatorio->set_formFocus('contatos');
    $relatorio->set_formLink('?');
    $relatorio->set_logServidor($idServidorPesquisado);
    $relatorio->set_logDetalhe("Visualizou a ficha cadastral");
    $relatorio->show();

    /*
     * Informações
     */

    # Pega o perfil do Servidor    
    $perfilServidor = $pessoal->get_idPerfil($idServidorPesquisado);
    $mensagem = null;

    if (($perfilServidor == 1) OR ($perfilServidor == 4)) {

        # Verifica o regime original do servidor (regime do concurso)
        $conc = new Concurso();
        $regime = $conc->get_regime($pessoal->get_idConcurso($idServidorPesquisado));
        $dtTranfRegime = $pessoal->get_dtTranfRegime($idServidorPesquisado);
        $dtadmissao = $pessoal->get_dtAdmissao($idServidorPesquisado);
        $dtEstagio = $pessoal->get_dtEstagio($idServidorPesquisado);
        $nomenclaturaOgiginal = $pessoal->get_nomenclaturaOriginal($idServidorPesquisado);
        $dadosConcurso = $pessoal->get_idConcurso($idServidorPesquisado);

        # Informa se o servidor entrou como CLT
        if ($regime == "CLT") {

            /*
             * Verifica se ele entrou antes da transformação em estatutário 09/09/2003 (Lei 4.152 de 08/09/2003)
             * Na pratica TODOS os servidores que entraram depois de 01/01/2002 já entraram como estatutários.
             */

            if (strtotime(date_to_bd($dtadmissao)) < strtotime(date_to_bd("01/01/2002"))) {
                if (empty($dadosConcurso)) {
                    $mensagem .= "- Servidor(a) admitido sob o regime CLT em {$dtadmissao}.<br/>";
                } else {
                    $mensagem .= "- Servidor(a) admitido mediante concurso público sob o regime CLT em {$dtadmissao}.<br/>";
                }
            }

            # Verifica se foi transformado
            if (!empty($dtTranfRegime)) {
                $mensagem .= "- Transformado em regime estatutário em {$dtTranfRegime}, conforme Lei 4.152 de 08/09/2003, publicada no DOERJ de 09/09/2003.";
            }
        }

        # Se for estatutário
        if ($regime == "Estatutário") {
            if (empty($dtEstagio) OR ($dtEstagio == $dtadmissao)) {
                $mensagem .= "- Servidor(a) admitido mediante concurso público sob o regime Estatutário em {$dtadmissao}";
            } else {
                $mensagem .= "- Servidor(a) designado, em {$dtEstagio}, para o estágio experimental, pelo período de 6 meses, e admitido mediante concurso público sob o regime Estatutário em {$dtadmissao}";
            }
        }

        # Informa se servidor optou da transferência FENORTE x UENF em 2002
        if ($pessoal->temOpcaoFenorteUenf($idServidorPesquisado)) {
            if (!is_null($pessoal->opcaoFenorteUenf($idServidorPesquisado))) {
                if (!empty($mensagem)) {
                    $mensagem .= "<br/>";
                }

                if ($pessoal->opcaoFenorteUenf($idServidorPesquisado)) {
                    $mensagem .= "- Transferência para UENF por opção do servidor a contar de 01/01/2002, conforme Lei nº 3684 de 23/10/2001.";
                } else {
                    $mensagem .= "- Transferido para UENF a contar de 16/06/2016 em virtude da extinção da FENORTE, conforme Lei 7237 de 16/03/2016.";
                }
            }
        }

        # Informa se houve alteração da nomenclatura do cargo
        if (!empty($nomenclaturaOgiginal)) {

            if (!empty($mensagem)) {
                $mensagem .= "<br/>";
            }

            # Informa sobre a mudança do nome do cargo
            $mensagem .= "- Mudança de Nomenclatura do Cargo efetivo conforme"
                    . " Decreto 28950 de 15/08/2001, Lei 4798/2006 de 30/06/2006 e "
                    . "Lei 4800/2006 de 30/06/2006. Nomenclatura Original do Cargo: <b>{$nomenclaturaOgiginal}<b/>";
        }

        if (!empty($mensagem)) {
            tituloRelatorio('Informações');
            p($mensagem, "pFichaCadastralMensagem");
            hr("rpa");
        }
    }


    /*
     * Dados Funcionais
     */

    tituloRelatorio('Dados Funcionais');

    $select = 'SELECT tbservidor.dtAdmissao,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbservidor.dtDemissao,
                      tbmotivo.motivo
                 FROM tbservidor LEFT OUTER JOIN tbconcurso ON (tbservidor.idConcurso = tbconcurso.idConcurso)
                                 LEFT JOIN tbmotivo ON (tbservidor.motivo = tbmotivo.idMotivo)             
                WHERE tbservidor.idServidor = ' . $idServidorPesquisado;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(null);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Admissão', 'Cargo - Área - Função (Comissão)', 'Concurso', 'Data de Saída', 'Motivo'));
    $relatorio->set_width(array(12, 30, 20, 12, 26));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array("date_to_php", null, null, "date_to_php"));
    $relatorio->set_classe(array(null, "Pessoal", "Pessoal"));
    $relatorio->set_metodo(array(null, "get_CargoCompleto2", "get_concursoRelatorio"));
    $relatorio->set_conteudo($result);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    #$relatorio->set_linhaNomeColuna(false);
    $relatorio->set_log(false);
    $relatorio->show();

    /*
     * Dados Financeiros
     */

    tituloRelatorio('Dados Financeiros');

    $trienioClasse = new Trienio();

    # pega os valores
    $salarioBase = $pessoal->get_salarioBase($idServidorPesquisado);                              // salário base
    $trienio = $trienioClasse->getValor($idServidorPesquisado);                                   // triênio
    $comissao = $pessoal->get_salarioCargoComissao($idServidorPesquisado);                        // cargo em comissão
    $gratificacao = $pessoal->get_gratificacao($idServidorPesquisado);                            // gratificação especial
    $total = $salarioBase + $trienio + $comissao + $gratificacao;
    $conteudo = array(array($salarioBase, $trienio, $comissao, $gratificacao, $total));

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(null);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Salário Base', 'Triênio', 'Cargo em Comissão', 'Gratificação Especial', 'Total'));
    $relatorio->set_width(array(20, 20, 20, 20, 20));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array('formataMoeda', 'formataMoeda', 'formataMoeda', 'formataMoeda', 'formataMoeda'));
    $relatorio->set_conteudo($conteudo);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    #$relatorio->set_linhaNomeColuna(false);
    $relatorio->set_log(false);
    $relatorio->show();

    /*
     * Dados dos Cedidos
     */

    # Pega o idPerfil da matricula
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);

    # Verifica se é Cedido
    if ($idPerfil == '2') {
        tituloRelatorio('Dados dos Cedidos');

        $select = 'SELECT orgaoOrigem,
                          matExterna,
                          onus,
                          salario,
                          processo,
                          dtPublicacao
                     FROM tbcedido
                    WHERE idServidor = ' . $idServidorPesquisado;

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Órgão de Origem', 'Matrícula Externa', 'Cedido com Ônus', 'Salário', 'Processo de Cessão', 'Publicação'));
        $relatorio->set_width(array(15, 15, 15, 10, 20, 15));
        $relatorio->set_align(array('cener'));
        $relatorio->set_funcao(array(null, null, null, 'formataMoeda', null, "date_to_php"));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Dados Pessoais
     */

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);

    tituloRelatorio('Dados Pessoais');

    $select = 'SELECT dtNasc,
                      tbnacionalidade.nacionalidade,
                      naturalidade,
                      tbestciv.estCiv,
                      sexo
                 FROM tbpessoa JOIN tbestciv ON (tbpessoa.estCiv = tbestciv.idEstCiv)
                               JOIN tbnacionalidade ON (tbpessoa.nacionalidade = tbnacionalidade.idNacionalidade)
                WHERE idPessoa = ' . $idPessoa;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(null);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Nascimento', 'Nacionalidade', 'Naturalidade', 'Estado Civil', 'Sexo'));
    $relatorio->set_width(array(20, 20, 20, 20, 20));
    $relatorio->set_funcao(array("date_to_php"));
    $relatorio->set_align(array('center'));
    $relatorio->set_conteudo($result);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    #$relatorio->set_linhaNomeColuna(false);
    $relatorio->set_log(false);
    $relatorio->show();

    /*
     * Filiação
     */

    tituloRelatorio('Filiação');

    $select = 'SELECT nomePai,
                      nomeMae 
                 FROM tbpessoa
                WHERE idPessoa = ' . $idPessoa;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(null);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Nome do Pai', 'Nome da Mãe'));
    $relatorio->set_width(array(50, 50));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array("trataNulo", "trataNulo"));
    $relatorio->set_conteudo($result);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    #$relatorio->set_linhaNomeColuna(false);
    $relatorio->set_log(false);
    $relatorio->show();

    /*
     * Documentação
     */

    tituloRelatorio('Documentação');

    $select = 'SELECT CPF,
                      concat(identidade," - ",
                      orgaoId," - ",
                      date_format(tbdocumentacao.dtId,"%d/%m/%Y")),
                      pisPasep,
                      concat(titulo," - ",zona," - ",secao)				         
                 FROM tbdocumentacao
                WHERE idPessoa = ' . $idPessoa;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(null);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('CPF', 'Identidade - Órgão - Emissão', 'PisPasep', 'Título de Eleitor - Zona - Seção'));
    $relatorio->set_width(array(20, 30, 20, 30));
    $relatorio->set_align(array('center'));
    #$relatorio->set_funcao($funcao);
    $relatorio->set_conteudo($result);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_exibeLinhaFinal(false);
    $relatorio->set_log(false);
    $relatorio->show();

    ##

    $select = 'SELECT motorista,
                      dtVencMotorista,
                      conselhoClasse,
                      registroClasse,
                      reservista 
                 FROM tbdocumentacao
                WHERE idPessoa = ' . $idPessoa;

    $result = $pessoal->select($select);

    if (!empty($result[0][0]) OR !empty($result[0][1]) OR !empty($result[0][2]) OR !empty($result[0][3]) OR !empty($result[0][4])) {

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Carteira Motorista', 'Vencimento', 'Conselho de Classe', 'Registro', 'Reservista'));
        $relatorio->set_width(array(20, 20, 20, 20, 20));
        $relatorio->set_align(array('center'));
        $relatorio->set_funcao(array("trataNulo", "date_to_php", "trataNulo", "trataNulo", "trataNulo"));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    ##

    $select = 'SELECT cp,
                      serieCp,
                      ufCp
                 FROM tbdocumentacao
                WHERE idPessoa = ' . $idPessoa;

    $result = $pessoal->select($select);

    if (!empty($result[0][0]) OR !empty($result[0][1]) OR !empty($result[0][2])) {

        br();
        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Carteira Profissional', 'Serie', 'UF'));
        $relatorio->set_width(array(30, 30, 30));
        $relatorio->set_align(array('center'));
        $relatorio->set_funcao(array("trataNulo", "trataNulo", "trataNulo"));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Endereço
     */

    tituloRelatorio('Endereço');

    $select = 'SELECT endereco,
                      bairro,
                      tbcidade.nome,
                      tbestado.uf,
                      cep 
                 FROM tbpessoa LEFT JOIN tbcidade USING (idCidade)
                               LEFT JOIN tbestado USING (idEstado)
                WHERE idPessoa = ' . $idPessoa;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(null);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(['Endereço', 'Bairro', 'Cidade', 'UF', 'Cep']);
    #$relatorio->set_width(array(80,20));
    $relatorio->set_align(['left', 'center']);
    #$relatorio->set_funcao($funcao);
    $relatorio->set_conteudo($result);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    #$relatorio->set_linhaNomeColuna(false);
    $relatorio->set_log(false);
    $relatorio->show();

    /*
     * Contatos
     */

    if ($postContatos) {
        tituloRelatorio('Contatos');

        $select = 'SELECT CONCAT("(",IFnull(telResidencialDDD,"--"),") ",IFnull(telResidencial,"---")),
                          CONCAT("(",IFnull(telCelularDDD,"--"),") ",IFnull(telCelular,"---")),
                          CONCAT("(",IFnull(telRecadosDDD,"--"),") ",IFnull(telRecados,"---")),
                          emailUenf,
                          emailPessoal,          
                          emailOutro          
                     FROM tbpessoa
                    WHERE idPessoa = ' . $idPessoa;

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Tel Residencial', 'Tel Celular', 'Tel Recado', 'Email Uenf', 'Email Pessoal', 'Outro E-mail'));
        #$relatorio->set_width(array(50,50));
        $relatorio->set_align(array('center'));
        $relatorio->set_funcao(array("trataNulo", "trataNulo", "trataNulo", "trataNulo", "trataNulo", "trataNulo"));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Dependentes
     */

    if ($postDependentes) {
        tituloRelatorio('Dependentes');

        $select = 'SELECT nome,
                          dtNasc,
                          tbparentesco.parentesco,
                          CASE sexo
                          WHEN "F" THEN "Feminino"
                          WHEN "M" THEN "Masculino"
                          end,
                          dependente,
                          auxCreche,
                          dtTermino
                     FROM tbdependente JOIN tbparentesco ON (tbparentesco.idParentesco = tbdependente.parentesco)
                    WHERE idPessoa=' . $idPessoa . '
                 ORDER BY dtNasc desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array("Nome", "Nascimento", "Parentesco", "Sexo", "Depend. no IR", "Auxílio Creche", "Término do Aux. Creche"));
        #$relatorio->set_width(array(30,10,10,10,10,10,10));
        $relatorio->set_funcao(array(null, "date_to_php", null, null, null, null, "date_to_php"));
        $relatorio->set_align(array('left', 'center'));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Formação
     */

    if ($postFormacao) {
        tituloRelatorio('Formação');

        $select = 'SELECT tbescolaridade.escolaridade,
                            habilitacao,
                            instEnsino,
                            anoTerm
                        FROM tbformacao join tbescolaridade USING (idEscolaridade)
                    WHERE idPessoa = ' . $idPessoa . '
                    ORDER BY anoterm desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Nível', 'Curso', 'Instituição', 'Término'));
        #$relatorio->set_width(array(20,35,35,10));
        $relatorio->set_align(array('left', 'left', 'left'));
        #$relatorio->set_funcao($funcao);
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Histórico de Lotações
     */

    if ($postLotacao) {
        tituloRelatorio('Histórico de Lotações');

        $select = 'SELECT tbhistlot.data,
                         concat(tblotacao.UADM,"-",tblotacao.DIR,"-",tblotacao.GER) as lotacao,
                         tbhistlot.motivo
                    FROM tblotacao join tbhistlot on (tblotacao.idLotacao = tbhistlot.lotacao)
                   WHERE tbhistlot.idservidor = ' . $idServidorPesquisado . '
                ORDER BY tbhistlot.data desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Data', 'Lotação', 'Motivo'));
        #$relatorio->set_width(array(20,40,40));
        $relatorio->set_funcao(array("date_to_php"));
        $relatorio->set_align(array('center', 'left', 'left'));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Histórico de Cargo em Comissão
     */

    if ($postCargo) {
        tituloRelatorio('Histórico de Cargos em Comissão');

        $select = 'SELECT concat(tbtipocomissao.descricao," - (",tbtipocomissao.simbolo,")") as comissao,
                          tbtipocomissao.valsal,
                          tbcomissao.dtNom,
                          tbcomissao.numProcNom,
                          tbcomissao.dtExo,
                          tbcomissao.numProcExo,
                          tbcomissao.dtPublicExo
                     FROM tbcomissao, tbtipocomissao
                    WHERE tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao 
                      AND idServidor = ' . $idServidorPesquisado . '
                 ORDER BY dtNom desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Cargo', 'Valor', 'Nomeação', 'Processo', 'Exoneração', 'Processo'));
        $relatorio->set_width(array(20, 10, 15, 20, 15, 20));
        $relatorio->set_funcao(array(null, 'formataMoeda', 'date_to_php', null, 'date_to_php'));
        $relatorio->set_align(array('left'));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Histórico de Progressão 
     */

    if ($postProgressao) {
        tituloRelatorio('Histórico de Progressões');

        $select = 'SELECT tbprogressao.dtInicial,
                         tbtipoprogressao.nome,
                         CONCAT(tbclasse.faixa," - ",tbclasse.valor) as vv,
                         tbprogressao.numProcesso,
                         tbprogressao.dtPublicacao
                    FROM tbprogressao JOIN tbtipoprogressao ON (tbprogressao.idTpProgressao = tbtipoprogressao.idTpProgressao)
                                      JOIN tbclasse ON (tbprogressao.idClasse = tbclasse.idClasse)
                    WHERE idServidor = ' . $idServidorPesquisado . '
                 ORDER BY tbprogressao.dtInicial desc, vv desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Data Inicial', 'Tipo', 'Valor', 'Processo', 'DOERJ'));
        #$relatorio->set_width(array(10,25,20,20,10,5));
        $relatorio->set_funcao(array('date_to_php', null, null, null, 'date_to_php'));
        $relatorio->set_align(array('center', 'left', 'center'));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Histórico de Triênio
     */

    if ($postTrienio) {
        tituloRelatorio('Histórico de Triênio');

        $select = 'SELECT dtInicial,
                          percentual,
                          numProcesso,
                          dtPublicacao
                     FROM tbtrienio
                    WHERE idServidor = ' . $idServidorPesquisado . '
                    ORDER BY dtInicial desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Data Inicial', 'Percentual (%)', 'Processo', 'DOERJ'));
        #$relatorio->set_width(array(20,20,20,20,20));
        $relatorio->set_funcao(array('date_to_php', null, null, 'date_to_php'));
        $relatorio->set_align(array('center'));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Histórico de Gratificação Especial
     */

    if ($postGratificacao) {
        tituloRelatorio('Histórico de Gratificação Especial');

        $select = 'SELECT dtInicial,
                          dtFinal,
                          valor,
                          processo
                     FROM tbgratificacao
                    WHERE idServidor = ' . $idServidorPesquisado . '
                    ORDER BY dtInicial desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Data Inicial', 'Data Final', 'Valor', 'Processo'));
        #$relatorio->set_width(array(25,25,25,25));
        $relatorio->set_funcao(array('date_to_php', 'date_to_php', 'formataMoeda'));
        $relatorio->set_align(array('left', 'left', 'left', 'left'));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Histórico de Direito pessoal
     */

    if ($postDireito) {
        tituloRelatorio('Histórico de Direito Pessoal');

        $select = 'SELECT dtInicial,
                          dtFinal,
                          valor,
                          processo,
                          dtPublicacao
                     FROM tbdireitopessoal
                    WHERE idServidor = ' . $idServidorPesquisado . '
                    ORDER BY dtInicial desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Data Inicial', 'Data Final', 'Valor', 'Processo', 'Publicaçao'));
        #$relatorio->set_width(array(25,25,25,25));
        $relatorio->set_funcao(array('date_to_php', 'date_to_php', 'formataMoeda', null, 'date_to_php'));
        $relatorio->set_align(array('center'));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Histórico de Férias
     */

    if ($postFerias) {
        tituloRelatorio('Histórico de Férias');

        $select = 'SELECT anoExercicio,
                          status,
                          dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1)
                     FROM tbferias
                    WHERE idServidor=' . $idServidorPesquisado . '
                    ORDER BY anoExercicio desc,dtInicial desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Exercício', 'Status', 'Data Inicial', 'Dias', 'Data Final'));
        #$relatorio->set_width(array(10,10,15,10,15,20,20));
        $relatorio->set_funcao(array(null, null, 'date_to_php', null, 'date_to_php'));
        $relatorio->set_align(array('center'));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Histórico de Afastamentos 
     */

    if ($postLicenca) {
        tituloRelatorio('Histórico de Afastamentos, Faltas e Licenças');

        // Retira as licenças 23, 34 e 29 da ficha a pedido de Rose
        $select = 'SELECT tbtipolicenca.nome,
                        CASE alta
                           WHEN 1 THEN "Sim"
                           WHEN 2 THEN "Não"
                           end,
                        dtInicial,
                        numdias,
                        ADDDATE(dtInicial,numDias-1),
                        CONCAT(tblicenca.idTpLicenca,"&",idLicenca),
                        dtPublicacao,
                        idLicenca
                   FROM tblicenca LEFT JOIN tbtipolicenca USING (idTpLicenca)
                  WHERE idServidor=' . $idServidorPesquisado . '
                    AND (idTpLicenca <> 23 AND idTpLicenca <> 34 AND idTpLicenca <> 29)
                  ORDER BY 3 desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array("Licença ou Afastamento", "Alta", "Inicio", "Dias", "Término", "Processo", "Publicação"));
        #$relatorio->set_width(array(22,10,2,10,10,6,15,10,5));
        $relatorio->set_funcao(array(null, null, 'date_to_php', null, 'date_to_php', 'exibeProcesso', 'date_to_php'));
        $relatorio->set_align(array('left', 'center'));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Histórico de Licença Especial (Prêmio)
     */

    if ($postLicenca) {
        tituloRelatorio('Histórico de Fruição de Licença Especial (Prêmio)');

        $select = 'SELECT idLicencaPremio,
                            dtInicial,
                            tblicencapremio.numdias,
                            ADDDATE(dtInicial,tblicencapremio.numDias-1),
                            CONCAT("6&",tblicencapremio.idServidor),
                            tbpublicacaopremio.dtPublicacao,
                            idLicencaPremio
                       FROM tblicencapremio LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                      WHERE tblicencapremio.idServidor = ' . $idServidorPesquisado . '
                    ORDER BY dtInicial desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array("Período Aquisitivo", "Inicio", "Dias", "Término", "Processo", "Publicação"));
        #$relatorio->set_width(array(22,10,2,10,10,6,15,10,5));
        $relatorio->set_funcao(array(null, 'date_to_php', null, 'date_to_php', 'exibeProcesso', 'date_to_php'));
        $relatorio->set_classe(array('LicencaPremio'));
        $relatorio->set_metodo(array('exibePeriodoAquisitivo'));
        #$relatorio->set_align(array('left'));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Observações adicionais de Licença Especial (Prêmio)
     */

    if ($postLicenca) {

        $select = "SELECT obsPremio
                     FROM tbservidor
                   WHERE idServidor = $idServidorPesquisado";

        $result = $pessoal->select($select);

        if (!empty($result[0][0])) {

            $relatorio = new Relatorio('relatorioFichaCadastral');
            #$relatorio->set_titulo(null);
            #$relatorio->set_subtitulo($subtitulo);
            $relatorio->set_label(array("Observações da Licença Prêmio"));
            #$relatorio->set_width(array(22,10,2,10,10,6,15,10,5));
            $relatorio->set_align(array("left"));
            $relatorio->set_funcao(array("nl2br"));
            $relatorio->set_conteudo($result);
            $relatorio->set_subTotal(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_log(false);
            $relatorio->show();
        }
    }


    /*
     * Histórico de Publicações de Licença Especial (Prêmio)
     */

    if ($postLicenca) {
        tituloRelatorio('Períodos Aquisitivos de Licença Especial (Prêmio)');

        $select = "SELECT dtPublicacao,
                        idPublicacaoPremio,
                        numDias,
                        idPublicacaoPremio,
                        idPublicacaoPremio,
                        idPublicacaoPremio
                   FROM tbpublicacaopremio
                   WHERE idServidor = $idServidorPesquisado
                ORDER BY dtInicioPeriodo desc";

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array("Data da Publicação", "Período Aquisitivo", "Dias Publicados", "Dias Fruídos", "Dias Disponíveis"));
        #$relatorio->set_width(array(22,10,2,10,10,6,15,10,5));
        $relatorio->set_align(array("center"));
        $relatorio->set_numeroOrdem(true);
        $relatorio->set_numeroOrdemTipo("d");
        $relatorio->set_funcao(array('date_to_php'));
        $relatorio->set_classe(array(null, 'LicencaPremio', null, 'LicencaPremio', 'LicencaPremio'));
        $relatorio->set_metodo(array(null, "exibePeriodoAquisitivo2", null, 'get_numDiasFruidosPorPublicacao', 'get_numDiasDisponiveisPorPublicacao'));
        $relatorio->set_conteudo($result);
        $relatorio->set_colunaSomatorio([2, 3, 4]);
        #$relatorio->set_colunaSomatorio(4);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }

    /*
     * Histórico de Licença Sem Vencimentos 
     */

    if ($postLicenca) {
        tituloRelatorio('Histórico de Licença Sem Vencimentos');

        $select = 'SELECT SUBSTRING(tbtipolicenca.nome,27),
                          CASE
                               WHEN optouContribuir = 1 THEN "Optou Pagar" 
                               WHEN optouContribuir = 2 THEN "Optou NÃO Pagar" 
                               ELSE "---"
                            END,
                            tblicencasemvencimentos.dtInicial,
                            tblicencasemvencimentos.numDias,
                            ADDDATE(tblicencasemvencimentos.dtInicial,tblicencasemvencimentos.numDias-1),
                            CONCAT(tblicencasemvencimentos.idTpLicenca,"&",idLicencasemvencimentos),
                            tblicencasemvencimentos.dtPublicacao,
                            idLicencasemvencimentos
                       FROM tblicencasemvencimentos JOIN tbtipolicenca USING (idTpLicenca)
                       WHERE tblicencasemvencimentos.idServidor = ' . $idServidorPesquisado . '
                  ORDER BY 3 desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        $relatorio->set_label(["Tipo", "RioPrevidência", "Inicio", "Dias", "Término", "Processo", "Publicação"]);
        $relatorio->set_funcao([null, null, 'date_to_php', null, 'date_to_php', 'exibeProcesso', 'date_to_php']);
        $relatorio->set_align(['left', 'center', 'center', 'center', 'center', 'left']);
        $relatorio->set_conteudo($result);        
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->set_log(false);
        $relatorio->set_textoMensagemSemRegistro("Não constam licenças sem vencimentos para o servidor");
        $relatorio->show();
    }

    /*
     * Tempo de Serviço Averbado
     */

    if ($postAverbacao) {
        tituloRelatorio('Tempo de Serviço Averbado');

        # Variáveis
        $empresaTipo = [
            [1, "Pública"],
            [2, "Privada"]
        ];

        $regime = [
            [1, "Celetista"],
            [2, "Estatutário"],
            [3, "Próprio"],
            [4, "Militar"]
        ];

        $select = "SELECT dtInicial,
                        dtFinal,
                        dias,
                        empresa,
                        CASE empresaTipo ";

        foreach ($empresaTipo as $tipo) {
            $select .= " WHEN {$tipo[0]} THEN '{$tipo[1]}' ";
        }

        $select .= "      END,
                      CASE regime ";
        foreach ($regime as $tipo2) {
            $select .= " WHEN {$tipo2[0]} THEN '{$tipo2[1]}' ";
        }

        $select .= "      END,
                        cargo,
                        dtPublicacao,
                        processo
                FROM tbaverbacao
                    WHERE idServidor= {$idServidorPesquisado}
                    ORDER BY dtInicial desc";

        $result = $pessoal->select($select);
        $relatorio = new Relatorio();
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);        
        $relatorio->set_label(array("Data Inicial", "Data Final", "Dias", "Empresa", "Tipo", "Regime", "Cargo", "Publicação", "Processo"));
        #$relatorio->set_width(array(10,10,5,20,8,10,8,10,3,15));
        $relatorio->set_funcao(array("date_to_php", "date_to_php", null, null, null, null, null, "date_to_php"));
        $relatorio->set_align(array('center', 'center', 'center', 'left'));
        $relatorio->set_conteudo($result);
        $relatorio->set_colunaSomatorio(2);
        #$relatorio->set_textoSomatorio("Total de Dias Averbados:");
        $relatorio->set_exibeSomatorioGeral(false);        
        #$relatorio->set_bordaInterna(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->set_log(false);
        $relatorio->set_textoMensagemSemRegistro("Não consta nenhum tempo averbado para o servidor");
        $relatorio->show();
    }

    /*
     * Histórico de Diária
     */

    if ($postDiaria) {
        tituloRelatorio('Histórico de Diária');

        $select = 'SELECT dataSaida,
                          dataChegada,
                          CONCAT(numeroCi,"/",YEAR(dataCi)),
                          processo,
                          dataProcesso,
                          origem,
                          destino,                                     
                          valor,
                          iddiaria
                     FROM tbdiaria 
                    WHERE idServidor=' . $idServidorPesquisado . '
                    ORDER BY dataSaida desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array("Saída", "Chegada", "CI", "Processo", "Data", "Origem", "Destino", "Valor"));
        #$relatorio->set_width(array(10,10,10,10,10,20,20,10));
        $relatorio->set_funcao(array("date_to_php", "date_to_php", null, null, "date_to_php", null, null, "formataMoeda"));
        $relatorio->set_align(array("center"));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->set_textoMensagemSemRegistro("Não constam diárias para o servidor");
        $relatorio->show();
    }

    /*
     * Abono Permanência
     */

    if ($postAbono) {
        tituloRelatorio('Abono Permanência');

        $select = 'SELECT processo,
                          dtPublicacao,
                          if(status = 1,"Deferido","Indeferido"),
                          data
                     FROM tbabono
                    WHERE idServidor = ' . $idServidorPesquisado . '
                    ORDER BY data desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Processo', 'Publicação', 'Status', 'Data Inicial'));
        #$relatorio->set_width(array(25,25,25,25));
        $relatorio->set_funcao(array(null, 'date_to_php', null, 'date_to_php'));
        $relatorio->set_align(array('center'));
        $relatorio->set_conteudo($result);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();
    }


    /*
     * Penalidades
     */

    if ($postPenalidade) {
        tituloRelatorio('Penalidades');

        $select = "SELECT data,
                          penalidade,
                          processo,
                          dtPublicacao,
                          pgPublicacao,
                          descricao
                     FROM tbpenalidade JOIN tbtipopenalidade USING (idTipoPenalidade)
                    WHERE idServidor={$idServidorPesquisado}
                 ORDER BY data desc";

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        $relatorio->set_label(array("Data", "Tipo", "Processo", "Publicação", "Pag", "Descrição"));
        $relatorio->set_width(array(10, 10, 15, 15, 5, 35));
        $relatorio->set_align(array("center", "center", "center", "center", "center", "left"));
        $relatorio->set_funcao(array("date_to_php", null, null, "date_to_php"));
        $relatorio->set_conteudo($result);        
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->set_log(false);
        $relatorio->set_textoMensagemSemRegistro("Não existem penalidades para esse servidor !");
        $relatorio->show();
    }

    /*
     * Elogios
     */

    if ($postElogio) {
        tituloRelatorio('Elogios');

        $select = "SELECT data,
                          descricao
                     FROM tbelogio
                    WHERE idServidor={$idServidorPesquisado}
                 ORDER BY data desc";

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        $relatorio->set_label(array("Data", "Descrição"));
        $relatorio->set_width(array(15, 85));
        $relatorio->set_align(array("center", "left"));
        $relatorio->set_funcao(array("date_to_php"));
        $relatorio->set_conteudo($result);        
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->set_log(false);
        $relatorio->set_textoMensagemSemRegistro("Não existem elogios para esse servidor !");
        $relatorio->show();
    }

    /*
     * Acumulação de Cargos
     */

    if ($postAcumulacao) {
        tituloRelatorio('Acumulação de Cargos');

        $select = "SELECT instituicao,
                          cargo,                                     
                          matricula,
                          idAcumulacao,
                          idAcumulacao,
                          dtSaida,
                          tbmotivo.motivo
                     FROM tbacumulacao LEFT JOIN tbmotivo ON(tbacumulacao.motivoSaida = tbmotivo.idMotivo)
                    WHERE idServidor = {$idServidorPesquisado}";

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        $relatorio->set_label(["Órgão", "Cargo", "Matrícula", "Resultado", "Publicação", "Saída", "Motivo"]);
        $relatorio->set_width([20, 20, 10, 10, 10, 10, 20]);
        #$relatorio->set_align(["left", "center", "left"]);
        $relatorio->set_funcao([null, null, null, null, null, "date_to_php"]);
        $relatorio->set_classe([null, null, null, "Acumulacao", "Acumulacao"]);
        $relatorio->set_metodo([null, null, null, "get_resultadoRelatorio", "exibePublicacao"]);
        $relatorio->set_conteudo($result); 
        
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->set_log(false);
        $relatorio->set_textoMensagemSemRegistro("Não existe acumulação de cargos cadastrados para esse servidor !");
        $relatorio->show();
    }

    if ($postDadosUsuario) {
        $intra = new Intra();
        $idServidorUsuario = $intra->get_idServidor($idUsuario);

        p('Emitido em: ' . date("d/m/Y - H:i:s"), 'pRelatorioDataImpressao');
        p(' por: ' . $pessoal->get_nome($idServidorUsuario) . ' - Id: ' . $pessoal->get_idFuncional($idServidorUsuario), 'pRelatorioDataImpressao');
        p($pessoal->get_cargo($idServidorUsuario), 'pRelatorioDataImpressao');
        p($pessoal->get_lotacao($idServidorUsuario), 'pRelatorioDataImpressao');
    } else {
        # Data da Impressão
        p('Emitido em: ' . date('d/m/Y - H:i:s') . " (" . $idUsuario . ")", 'pRelatorioDataImpressao');
    }

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}