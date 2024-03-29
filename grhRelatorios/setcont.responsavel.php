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
     * Qualificação
     */
    
    $select = 'SELECT tbpessoa.nome,
                      nomePai,
                      nomeMae,
                      dtNasc,
                      naturalidade
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                 LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                                 LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                WHERE tbservidor.idServidor = ' . $idServidorPesquisado;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    $relatorio->set_titulo('Relatório Para o Cadastro de Responsáveis');
    $relatorio->set_topico("1 - Qualificação");
    $relatorio->set_label(['Nome', 'Pai', 'Mãe', 'Nascimento', 'Naturalidade']);
    $relatorio->set_funcao([null, null, null, "date_to_php"]);
    #$relatorio->set_classe([null, null, "pessoal"]);
    #$relatorio->set_metodo([null, null, "get_lotacao"]);    
    $relatorio->set_conteudo($result);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_linhaNomeColuna(false);
    $relatorio->set_brHr(0);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    #$relatorio->set_cabecalhoRelatorio(false);
    #$relatorio->set_menuRelatorio(false);
    $relatorio->set_log(false);
    $relatorio->show();
    br();

    /*
     * Documentação
     */

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
    
    $select = 'SELECT concat(identidade," - ",
                      orgaoId," - ",
                      date_format(tbdocumentacao.dtId,"%d/%m/%Y")),
                      CPF,
                      concat(titulo," - ",zona," - ",secao)				         
                 FROM tbdocumentacao
                WHERE idPessoa = ' . $idPessoa;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    $relatorio->set_topico("2 - Documentação");
    $relatorio->set_label(['Identidade - Órgão - Emissão', 'CPF', 'Título de Eleitor - Zona - Seção']);
    $relatorio->set_conteudo($result);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_log(false);
    $relatorio->show();
    br();

    /*
     * Se Servidor
     */
    
    $select = 'SELECT matricula,
                      tbservidor.idFuncional,
                      tbservidor.idServidor,
                      tbservidor.idServidor,
                      tbservidor.idServidor
                 FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                 LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                                 LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idSituacao)
                WHERE tbservidor.idServidor = ' . $idServidorPesquisado;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    $relatorio->set_topico("3 - Dados do Servidor");
    $relatorio->set_label(['Matrícula', 'IdFuncional', 'Cargo Efetivo', 'Cargo em Comissão', 'Lotaçao']);
    $relatorio->set_funcao(["dv"]);
    $relatorio->set_classe([null, null, "pessoal", "pessoal", "pessoal"]);
    $relatorio->set_metodo([null, null, "get_cargoEfetivo", 'get_cargoComissao2', "get_lotacao"]);
    $relatorio->set_conteudo($result);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_linhaNomeColuna(false);
    $relatorio->set_brHr(0);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_log(false);
    $relatorio->show();
    br();

    /*
     * Endereço
     */

    $select = 'SELECT endereco,
                      bairro,
                      tbcidade.nome,
                      tbestado.uf,
                      cep,
                      CONCAT("(",IFnull(telResidencialDDD,"--"),") ",IFnull(telResidencial,"---")),
                      CONCAT("(",IFnull(telCelularDDD,"--"),") ",IFnull(telCelular,"---"))
                 FROM tbpessoa LEFT JOIN tbcidade USING (idCidade)
                               LEFT JOIN tbestado USING (idEstado)
                WHERE idPessoa = ' . $idPessoa;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    $relatorio->set_topico("4 - Endereço Residencial");
    $relatorio->set_label(['Endereço', 'Bairro', 'Cidade', 'UF', 'Cep', 'Telefone', 'Celular']);
    $relatorio->set_align(['left', 'center']);
    $relatorio->set_conteudo($result);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    $relatorio->set_log(false);
    $relatorio->show();

    # Data da Impressão
    p('Emitido em: ' . date('d/m/Y - H:i:s') . " (" . $idUsuario . ")", 'pRelatorioDataImpressao');

    $grid->fechaColuna();
    $grid->fechaGrid();

    $page->terminaPagina();
}