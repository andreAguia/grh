<?php
/**
 * Sistema GRH
 * 
 * Ficha Cadastral
 *   
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("../grhSistema/_config.php");

# Verifica qual será o id
if(is_null($idServidorPesquisado)){
    $idFicha = $idUsuario;
}else{
    $idFicha = $idServidorPesquisado;
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

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario);

if($acesso){    
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
                WHERE tbservidor.idServidor = '.$idFicha;

    $result = $pessoal->select($select);   

    $relatorio = new Relatorio('relatorioFichaCadastral');
    $relatorio->set_titulo('Ficha Cadastral');
    $relatorio->set_label(array('IdFuncional','Matrícula','Nome','Lotaçao','Perfil','Situação'));
    #$relatorio->set_width(array(15,10,40,15,20));
    $relatorio->set_funcao(array(NULL,"dv"));
    $relatorio->set_classe(array(NULL,NULL,NULL,"pessoal"));
    $relatorio->set_metodo(array(NULL,NULL,NULL,"get_lotacao"));
    $relatorio->set_align(array('center'));
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    #$relatorio->set_botaoVoltar(FALSE);
    #$relatorio->set_bordaInterna(TRUE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_linhaNomeColuna(FALSE);
    $relatorio->set_brHr(0);
    $relatorio->set_formCampos(array(
              array ('nome' => 'contatos',
                     'label' => 'Contatos',
                     'tipo' => 'simnao',
                     'valor' => $postContatos,
                     'size' => 5,
                     'title' => 'Exibe os Contatos do Servidor (Telefones, Emails, etc)',
                     'onChange' => 'formPadrao.submit();',
                     'col' => 3,
                     'linha' => 1),              
              array ('nome' => 'formacao',
                     'label' => 'Formação.',
                     'tipo' => 'simnao',
                     'size' => 5,
                     'title' => 'Exibe a Área de Foemação Educacional do servidor',
                     'valor' => $postFormacao,
                     'onChange' => 'formPadrao.submit();',
                     'col' => 3,
                     'linha' => 1),
              array ('nome' => 'lotacao',
                     'label' => 'Lotação',
                     'tipo' => 'simnao',
                     'size' => 5,
                     'title' => 'Exibe o Histórico de Lotação do Servidor',
                     'valor' => $postLotacao,
                     'col' => 3,
                     'onChange' => 'formPadrao.submit();',
                     'linha' => 1),
              array ('nome' => 'dependentes',
                     'label' => 'Depend.',
                     'tipo' => 'simnao',
                     'size' => 5,
                     'title' => 'Exibe os Dependentes do Servidor',
                     'valor' => $postDependentes,
                     'onChange' => 'formPadrao.submit();',
                     'col' => 3,
                     'linha' => 1),
              array ('nome' => 'cargo',
                     'label' => 'Cargos em Comissão',
                     'tipo' => 'simnao',
                     'size' => 5,
                     'title' => 'Exibe o Histórico de Cargos em Comissão',
                     'valor' => $postCargo,
                     'onChange' => 'formPadrao.submit();',
                     'col' => 3,
                     'linha' => 2),            
              array ('nome' => 'trienio',
                     'label' => 'Triênio',
                     'tipo' => 'simnao',
                     'size' => 5,
                     'title' => 'Exibe o Histórico de Triênio',
                     'valor' => $postTrienio,
                     'onChange' => 'formPadrao.submit();',
                     'col' => 3,
                     'linha' => 2),
              array ('nome' => 'ferias',
                     'label' => 'Férias',
                     'tipo' => 'simnao',
                     'size' => 1,
                     'title' => 'Exibe o Histórico de Férias',
                     'valor' => $postFerias,
                     'onChange' => 'formPadrao.submit();',
                     'col' => 3,
                     'linha' => 2),
              array ('nome' => 'licenca',
                     'label' => 'Licença',
                     'tipo' => 'simnao',
                     'size' => 1,
                     'title' => 'Exibe o Histórico de Licença',
                     'valor' => $postLicenca,
                     'onChange' => 'formPadrao.submit();',
                     'col' => 3,
                     'linha' => 2),              
              array ('nome' => 'progressao',
                     'label' => 'Progre.',
                     'tipo' => 'simnao',
                     'size' => 1,
                     'title' => 'Exibe o Histórico de Progressões e Enquadramento',
                     'valor' => $postProgressao,
                     'onChange' => 'formPadrao.submit();',
                     'col' => 3,
                     'linha' => 3),
              array ('nome' => 'gratificacao',
                     'label' => 'Gratif.',
                     'tipo' => 'simnao',
                     'size' => 1,
                     'title' => 'Exibe o Histórico de Gratificação Especial',
                     'valor' => $postGratificacao,
                     'onChange' => 'formPadrao.submit();',
                     'col' => 3,
                     'linha' => 3),
              array ('nome' => 'diaria',
                     'label' => 'Diária',
                     'tipo' => 'simnao',
                     'size' => 1,
                     'title' => 'Exibe o Histórico de Diária',
                     'valor' => $postDiaria,
                     'onChange' => 'formPadrao.submit();',
                     'col' => 3,
                     'linha' => 3),
              array ('nome' => 'averbacao',
                     'label' => 'Tempo de Serviço',
                     'tipo' => 'simnao',
                     'size' => 1,
                     'title' => 'Exibe o Tempo de Serviço Averbado e Cadastrado no SAPE',
                     'valor' => $postAverbacao,
                     'onChange' => 'formPadrao.submit();',
                     'col' => 3,
                     'linha' => 3)));

    $relatorio->set_formFocus('contatos');		
    $relatorio->set_formLink('?');
    $relatorio->set_logServidor($idFicha);
    $relatorio->set_logDetalhe("Visualizou a Ficha Cadastral");
    $relatorio->show();        

    /*
     * Dados Funcionais
     */

    tituloRelatorio('Dados Funcionais');

    $select = 'SELECT tbservidor.dtAdmissao,
                      tbservidor.idServidor,
                      CONCAT(tbconcurso.anobase," - ",tbconcurso.orgExecutor) as concurso,
                      tbservidor.dtDemissao,
                      tbmotivo.motivo
                 FROM tbservidor LEFT OUTER JOIN tbconcurso ON (tbservidor.idConcurso = tbconcurso.idConcurso)
                                 LEFT JOIN tbmotivo ON (tbservidor.motivo = tbmotivo.idMotivo)             
                WHERE tbservidor.idServidor = '.$idFicha;

    $result = $pessoal->select($select);   

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(NULL);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Data Admissão','Cargo','Concurso','Data de Saída','Motivo'));
    $relatorio->set_width(array(12,30,20,12,26));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array("date_to_php",NULL,NULL,"date_to_php"));
    $relatorio->set_classe(array(NULL,"Pessoal"));
    $relatorio->set_metodo(array(NULL,"get_Cargo"));
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(FALSE);
    #$relatorio->set_bordaInterna(TRUE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    #$relatorio->set_linhaNomeColuna(FALSE);
    $relatorio->set_log(FALSE);    
    $relatorio->show();

    /*
     * Dados Financeiros
     */

    tituloRelatorio('Dados Financeiros');

    # pega os valores
    $salarioBase = $pessoal->get_salarioBase($idFicha);                              // salário base
    $trienio = ($salarioBase * ($pessoal->get_trienioPercentual($idFicha)))/100;     // triênio
    $comissao = $pessoal->get_salarioCargoComissao($idFicha);                        // cargo em comissão
    $gratificacao = $pessoal->get_gratificacao($idFicha);                            // gratificação especial
    $total = $salarioBase + $trienio + $comissao + $gratificacao;
    $conteudo = array(array($salarioBase,$trienio,$comissao,$gratificacao,$total));

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(NULL);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Salário Base','Triênio','Cargo em Comissão','Gratificação Especial','Total'));
    $relatorio->set_width(array(20,20,20,20,20));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array('formataMoeda','formataMoeda','formataMoeda','formataMoeda','formataMoeda'));
    $relatorio->set_conteudo($conteudo);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(FALSE);
    #$relatorio->set_bordaInterna(TRUE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    #$relatorio->set_linhaNomeColuna(FALSE);
    $relatorio->set_log(FALSE);
    $relatorio->show();

    /*
     * Dados dos Cedidos
     */

    # Pega o idPerfil da matricula
    $idPerfil = $pessoal->get_idPerfil($idFicha);

    # Verifica se é Cedido
    if ($idPerfil == '2'){
        tituloRelatorio('Dados dos Cedidos');

        $select = 'SELECT orgaoOrigem,
                          matExterna,
                          onus,
                          salario,
                          processo,
                          dtPublicacao
                     FROM tbcedido
                    WHERE idServidor = '.$idFicha;

        $result = $pessoal->select($select);    

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(NULL);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Órgão de Origem','Matrícula Externa','Cedido com Ônus','Salário','Processo de Cessão','Publicação'));
        $relatorio->set_width(array(15,15,15,10,20,15));    
        $relatorio->set_align(array('cener'));
        $relatorio->set_funcao(array(NULL,NULL,NULL,'formataMoeda',NULL,"date_to_php"));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(FALSE);
        #$relatorio->set_bordaInterna(TRUE);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_totalRegistro(FALSE);
        $relatorio->set_dataImpressao(FALSE);
        $relatorio->set_cabecalhoRelatorio(FALSE);
        $relatorio->set_menuRelatorio(FALSE);    
        #$relatorio->set_linhaNomeColuna(FALSE);
        $relatorio->set_log(FALSE);
        $relatorio->show();
    }

    /*
     * Dados Pessoais
     */

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idFicha);		

    tituloRelatorio('Dados Pessoais');

    $select = 'SELECT dtNasc,
                      tbnacionalidade.nacionalidade,
                      naturalidade,
                      tbestciv.estCiv,
                      sexo
                 FROM tbpessoa JOIN tbestciv ON (tbpessoa.estCiv = tbestciv.idEstCiv)
                               JOIN tbnacionalidade ON (tbpessoa.nacionalidade = tbnacionalidade.idNacionalidade)
                WHERE idPessoa = '.$idPessoa;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(NULL);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Nascimento','Nacionalidade','Naturalidade','Estado Civil','Sexo'));
    $relatorio->set_width(array(20,20,20,20,20));
    $relatorio->set_funcao(array("date_to_php"));
    $relatorio->set_align(array('center'));
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(FALSE);
    #$relatorio->set_bordaInterna(TRUE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    #$relatorio->set_linhaNomeColuna(FALSE);
    $relatorio->set_log(FALSE);
    $relatorio->show();

    /*
     * Filiação
     */

    tituloRelatorio('Filiação');

    $select = 'SELECT nomePai,
                      nomeMae 
                 FROM tbpessoa
                WHERE idPessoa = '.$idPessoa;

    $result = $pessoal->select($select);
    
    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(NULL);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Nome do Pai','Nome da Mãe'));
    $relatorio->set_width(array(50,50));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array("trataNulo","trataNulo"));
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(FALSE);
    #$relatorio->set_bordaInterna(TRUE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    #$relatorio->set_linhaNomeColuna(FALSE);
    $relatorio->set_log(FALSE);
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
                WHERE idPessoa = '.$idPessoa;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(NULL);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('CPF','Identidade - Órgão - Emissão','PisPasep','Título de Eleitor - Zona - Seção'));
    $relatorio->set_width(array(20,30,20,30));
    $relatorio->set_align(array('center'));
    #$relatorio->set_funcao($funcao);
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(FALSE);
    #$relatorio->set_bordaInterna(TRUE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    #$relatorio->set_linhaNomeColuna(FALSE);
    $relatorio->set_log(FALSE);
    $relatorio->show();

    ##

    $select = 'SELECT motorista,
                      dtVencMotorista,
                      conselhoClasse,
                      registroClasse,
                      reservista 
                 FROM tbdocumentacao
                WHERE idPessoa = '.$idPessoa;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(NULL);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Carteira Motorista','Vencimento','Conselho de Classe','Registro','Reservista'));
    $relatorio->set_width(array(20,20,20,20,20));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array("trataNulo","date_to_php","trataNulo","trataNulo","trataNulo"));
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(FALSE);
    #$relatorio->set_bordaInterna(TRUE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    #$relatorio->set_linhaNomeColuna(FALSE);
    $relatorio->set_log(FALSE);
    $relatorio->show();
    
    ##

    $select = 'SELECT cp,
                      serieCp,
                      ufCp
                 FROM tbdocumentacao
                WHERE idPessoa = '.$idPessoa;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(NULL);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Carteira Profissional','Serie','UF'));
    $relatorio->set_width(array(30,30,30));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array("trataNulo","trataNulo","trataNulo"));
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(FALSE);
    #$relatorio->set_bordaInterna(TRUE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    #$relatorio->set_linhaNomeColuna(FALSE);
    $relatorio->set_log(FALSE);
    $relatorio->show();

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
                WHERE idPessoa = '.$idPessoa;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(NULL);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(['Endereço','Bairro','Cidade','UF','Cep']);
    #$relatorio->set_width(array(80,20));
    $relatorio->set_align(['left','center']);
    #$relatorio->set_funcao($funcao);
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(FALSE);
    #$relatorio->set_bordaInterna(TRUE);
    $relatorio->set_subTotal(FALSE);
    $relatorio->set_totalRegistro(FALSE);
    $relatorio->set_dataImpressao(FALSE);
    $relatorio->set_cabecalhoRelatorio(FALSE);
    $relatorio->set_menuRelatorio(FALSE);
    #$relatorio->set_linhaNomeColuna(FALSE);
    $relatorio->set_log(FALSE);
    $relatorio->show();

    /*
     * Contatos
     */

    if($postContatos){
        tituloRelatorio('Contatos');

        $select = 'SELECT CONCAT("(",IFNULL(telResidencialDDD,"--"),") ",IFNULL(telResidencial,"---")),
                          CONCAT("(",IFNULL(telCelularDDD,"--"),") ",IFNULL(telCelular,"---")),
                          CONCAT("(",IFNULL(telRecadosDDD,"--"),") ",IFNULL(telRecados,"---")),
                          emailUenf,
                          emailPessoal          
                     FROM tbpessoa
                    WHERE idPessoa = '.$idPessoa;

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(NULL);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Tel Residencial','Tel Celular','Tel Recado','Email Uenf','Email Pessoal'));
        #$relatorio->set_width(array(50,50));
        $relatorio->set_align(array('center'));
        $relatorio->set_funcao(array("trataNulo","trataNulo","trataNulo","trataNulo","trataNulo"));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(FALSE);
        #$relatorio->set_bordaInterna(TRUE);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_totalRegistro(FALSE);
        $relatorio->set_dataImpressao(FALSE);
        $relatorio->set_cabecalhoRelatorio(FALSE);
        $relatorio->set_menuRelatorio(FALSE);
        #$relatorio->set_linhaNomeColuna(FALSE);
        $relatorio->set_log(FALSE);
        $relatorio->show();
    }

    /*
     * Dependentes
     */

    if($postDependentes){
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
                    WHERE idPessoa='.$idPessoa.'
                 ORDER BY dtNasc desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(NULL);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array("Nome","Nascimento","Parentesco","Sexo","Depend. no IR","Auxílio Creche","Término do Aux. Creche"));
        #$relatorio->set_width(array(30,10,10,10,10,10,10));
        $relatorio->set_funcao(array(NULL,"date_to_php",NULL,NULL,NULL,NULL,"date_to_php"));
        $relatorio->set_align(array('left','center'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(FALSE);
        #$relatorio->set_bordaInterna(TRUE);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_totalRegistro(TRUE);
        $relatorio->set_dataImpressao(FALSE);
        $relatorio->set_cabecalhoRelatorio(FALSE);
        $relatorio->set_menuRelatorio(FALSE);
        #$relatorio->set_linhaNomeColuna(FALSE);
        $relatorio->set_log(FALSE);
        $relatorio->show();
    }

    /*
     * Formação
     */

    if($postFormacao){
        tituloRelatorio('Formação');

        $select = 'SELECT tbescolaridade.Escolaridade,
                            habilitacao,
                            instEnsino,
                            anoTerm
                        FROM tbformacao join tbescolaridade on (tbformacao.escolaridade = tbescolaridade.idEscolaridade)
                    WHERE idPessoa = '.$idPessoa.'
                    ORDER BY anoterm desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(NULL);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Nível','Curso','Instituição','Término'));
        #$relatorio->set_width(array(20,35,35,10));
        $relatorio->set_align(array('left','left','left'));
        #$relatorio->set_funcao($funcao);
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(FALSE);
        #$relatorio->set_bordaInterna(TRUE);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_totalRegistro(TRUE);
        $relatorio->set_dataImpressao(FALSE);
        $relatorio->set_cabecalhoRelatorio(FALSE);
        $relatorio->set_menuRelatorio(FALSE);
        #$relatorio->set_linhaNomeColuna(FALSE);
        $relatorio->set_log(FALSE);
        $relatorio->show();
    }

    /*
     * Histórico de Lotações
     */

    if($postLotacao){
        tituloRelatorio('Histórico de Lotações');

        $select ='SELECT tbhistlot.data,
                         concat(tblotacao.UADM,"-",tblotacao.DIR,"-",tblotacao.GER) as lotacao,
                         tbhistlot.motivo
                    FROM tblotacao join tbhistlot on (tblotacao.idLotacao = tbhistlot.lotacao)
                   WHERE tbhistlot.idservidor = '.$idFicha.'
                ORDER BY tbhistlot.data desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(NULL);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Data','Lotação','Motivo'));
        #$relatorio->set_width(array(20,40,40));
        $relatorio->set_funcao(array("date_to_php"));
        $relatorio->set_align(array('center','left','left'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(FALSE);
        #$relatorio->set_bordaInterna(TRUE);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_totalRegistro(TRUE);
        $relatorio->set_dataImpressao(FALSE);
        $relatorio->set_cabecalhoRelatorio(FALSE);
        $relatorio->set_menuRelatorio(FALSE);
        #$relatorio->set_linhaNomeColuna(FALSE);
        $relatorio->set_log(FALSE);
        $relatorio->show();
    }

    /*
     * Histórico de Cargo em Comissão
     */

    if($postCargo){
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
                      AND idServidor = '.$idFicha.'
                 ORDER BY dtNom desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(NULL);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Cargo','Valor','Nomeação','Processo','Exoneração','Processo'));
        $relatorio->set_width(array(20,10,15,20,15,20));
        $relatorio->set_funcao(array(NULL,'formataMoeda','date_to_php',NULL,'date_to_php'));
        $relatorio->set_align(array('left'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(FALSE);
        #$relatorio->set_bordaInterna(TRUE);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_totalRegistro(TRUE);
        $relatorio->set_dataImpressao(FALSE);
        $relatorio->set_cabecalhoRelatorio(FALSE);
        $relatorio->set_menuRelatorio(FALSE);
        #$relatorio->set_linhaNomeColuna(FALSE);
        $relatorio->set_log(FALSE);
        $relatorio->show();
    }

    /*
     * Histórico de Progressão e Enquadramento 
     */

    if($postProgressao){
        tituloRelatorio('Histórico de Progressões e Enquadramentos');

        $select ='SELECT tbprogressao.dtInicial,
                         tbtipoprogressao.nome,
                         CONCAT(tbclasse.faixa," - ",tbclasse.valor) as vv,
                         tbprogressao.numProcesso,
                         tbprogressao.dtPublicacao
                    FROM tbprogressao JOIN tbtipoprogressao ON (tbprogressao.idTpProgressao = tbtipoprogressao.idTpProgressao)
                                      JOIN tbclasse ON (tbprogressao.idClasse = tbclasse.idClasse)
                    WHERE idServidor = '.$idFicha.'
                 ORDER BY tbprogressao.dtInicial desc, vv desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(NULL);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Data Inicial','Tipo','Valor','Processo','DOERJ'));
        #$relatorio->set_width(array(10,25,20,20,10,5));
        $relatorio->set_funcao(array('date_to_php',NULL,NULL,NULL,'date_to_php'));
        $relatorio->set_align(array('center','left','center'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(FALSE);
        #$relatorio->set_bordaInterna(TRUE);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_totalRegistro(TRUE);
        $relatorio->set_dataImpressao(FALSE);
        $relatorio->set_cabecalhoRelatorio(FALSE);
        $relatorio->set_menuRelatorio(FALSE);
        #$relatorio->set_linhaNomeColuna(FALSE);
        $relatorio->set_log(FALSE);
        $relatorio->show();
    }

    /*
     * Histórico de Triênio
     */

    if($postTrienio){
        tituloRelatorio('Histórico de Triênio');

        $select = 'SELECT dtInicial,
                          percentual,
                          numProcesso,
                          dtPublicacao
                     FROM tbtrienio
                    WHERE idServidor = '.$idFicha.'
                    ORDER BY dtInicial desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(NULL);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Data Inicial','Percentual (%)','Processo','DOERJ'));
        #$relatorio->set_width(array(20,20,20,20,20));
        $relatorio->set_funcao(array('date_to_php',NULL,NULL,'date_to_php'));
        $relatorio->set_align(array('center'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(FALSE);
        #$relatorio->set_bordaInterna(TRUE);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_totalRegistro(TRUE);
        $relatorio->set_dataImpressao(FALSE);
        $relatorio->set_cabecalhoRelatorio(FALSE);
        $relatorio->set_menuRelatorio(FALSE);
        #$relatorio->set_linhaNomeColuna(FALSE);
        $relatorio->set_log(FALSE);
        $relatorio->show();
    }

    /*
     * Histórico de Gratificação Especial
     */

    if($postGratificacao){
        tituloRelatorio('Histórico de Gratificação Especial');

        $select = 'SELECT dtInicial,
                          dtFinal,
                          valor,
                          processo
                     FROM tbgratificacao
                    WHERE idServidor = '.$idFicha.'
                    ORDER BY dtInicial desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(NULL);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Data Inicial','Data Final','Valor','Processo'));
        #$relatorio->set_width(array(25,25,25,25));
        $relatorio->set_funcao(array('date_to_php','date_to_php','formataMoeda'));
        $relatorio->set_align(array('left','left','left','left'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(FALSE);
        #$relatorio->set_bordaInterna(TRUE);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_totalRegistro(TRUE);
        $relatorio->set_dataImpressao(FALSE);
        $relatorio->set_cabecalhoRelatorio(FALSE);
        $relatorio->set_menuRelatorio(FALSE);
        #$relatorio->set_linhaNomeColuna(FALSE);
        $relatorio->set_log(FALSE);
        $relatorio->show();
    }

    /*
     * Histórico de Férias
     */
    
    if($postFerias){
        tituloRelatorio('Histórico de Férias');

        $select = 'SELECT anoExercicio,
                          status,
                          dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1)
                     FROM tbferias
                    WHERE idServidor='.$idFicha.'
                    ORDER BY anoExercicio desc,dtInicial desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(NULL);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Exercício','Status','Data Inicial','Dias','Data Final'));
        #$relatorio->set_width(array(10,10,15,10,15,20,20));
        $relatorio->set_funcao(array(NULL,NULL,'date_to_php',NULL,'date_to_php'));
        $relatorio->set_align(array('center'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(FALSE);
        #$relatorio->set_bordaInterna(TRUE);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_totalRegistro(TRUE);
        $relatorio->set_dataImpressao(FALSE);
        $relatorio->set_cabecalhoRelatorio(FALSE);
        $relatorio->set_menuRelatorio(FALSE);
        #$relatorio->set_linhaNomeColuna(FALSE);
        $relatorio->set_log(FALSE);
        $relatorio->show();
    }

    /*
     * Histórico de Afastamentos 
     */
    
    if($postLicenca){
        tituloRelatorio('Histórico de Afastamentos, Faltas e Licenças');

        $select = '(SELECT CONCAT(tbtipolicenca.nome," - ",IFNULL(tbtipolicenca.lei,"")),
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
                                FROM tblicenca LEFT JOIN tbtipolicenca ON tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca
                               WHERE idServidor='.$idFicha.')
                               UNION
                               (SELECT (SELECT CONCAT(tbtipolicenca.nome," - ",IFNULL(tbtipolicenca.lei,"")) FROM tbtipolicenca WHERE idTpLicenca = 6),
                                       "",
                                       dtInicial,
                                       tblicencapremio.numdias,
                                       ADDDATE(dtInicial,tblicencapremio.numDias-1),
                                       CONCAT("6&",tblicencapremio.idServidor),
                                       tbpublicacaopremio.dtPublicacao,
                                       idLicencaPremio
                                  FROM tblicencapremio LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                                 WHERE tblicencapremio.idServidor = '.$idFicha.')
                              ORDER BY 3 desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(NULL);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array("Licença ou Afastamento","Alta","Inicio","Dias","Término","Processo","Publicação"));
        #$relatorio->set_width(array(22,10,2,10,10,6,15,10,5));
        $relatorio->set_funcao(array(NULL,NULL,'date_to_php',NULL,'date_to_php','exibeProcessoPremio','date_to_php'));
        $relatorio->set_align(array('left','center'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(FALSE);
        #$relatorio->set_bordaInterna(TRUE);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_totalRegistro(TRUE);
        $relatorio->set_dataImpressao(FALSE);
        $relatorio->set_cabecalhoRelatorio(FALSE);
        $relatorio->set_menuRelatorio(FALSE);
        #$relatorio->set_linhaNomeColuna(FALSE);
        $relatorio->set_log(FALSE);
        $relatorio->show();
    }    
    
    /*
     * Tempo de Serviço Averbado
     */

    if($postAverbacao){
        tituloRelatorio('Tempo de Serviço Averbado');

        $select = 'SELECT dtInicial,
                        dtFinal,
                        dias,
                        empresa,
                        CASE empresaTipo
                        WHEN 1 THEN "Pública"
                        WHEN 2 THEN "Privada"
                        END,
                        CASE regime
                        WHEN 1 THEN "Celetista"
                        WHEN 2 THEN "Estatutário"
                        END,
                        cargo,
                        dtPublicacao,
                        processo
                FROM tbaverbacao
                    WHERE idServidor='.$idFicha.'
                    ORDER BY dtInicial desc';

        $result = $pessoal->select($select);
        $relatorio = new Relatorio();
        #$relatorio->set_titulo(NULL);
        #$relatorio->set_subtitulo($subtitulo);        
        $relatorio->set_label(array("Data Inicial","Data Final","Dias","Empresa","Tipo","Regime","Cargo","Publicação","Processo"));
        #$relatorio->set_width(array(10,10,5,20,8,10,8,10,3,15));
        $relatorio->set_funcao(array("date_to_php","date_to_php",NULL,NULL,NULL,NULL,NULL,"date_to_php"));
        #$relatorio->set_align(array('left','left','left','left','left','left','left','left','left','Left'));
        $relatorio->set_conteudo($result);
        $relatorio->set_colunaSomatorio(2);
        #$relatorio->set_textoSomatorio("Total de Dias Averbados:");
        $relatorio->set_exibeSomatorioGeral(FALSE);
        $relatorio->set_botaoVoltar(FALSE);
        #$relatorio->set_bordaInterna(TRUE);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_totalRegistro(TRUE);
        $relatorio->set_dataImpressao(FALSE);
        $relatorio->set_cabecalhoRelatorio(FALSE);
        $relatorio->set_menuRelatorio(FALSE);
        $relatorio->set_log(FALSE);
        $relatorio->show();
    } 
    
    /*
     * Histórico de Diária
     */

    if($postDiaria){
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
                    WHERE idServidor='.$idFicha.'
                    ORDER BY dataSaida desc';


        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(NULL);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array("Saída","Chegada","CI","Processo","Data","Origem","Destino","Valor"));
        #$relatorio->set_width(array(10,10,10,10,10,20,20,10));
        $relatorio->set_funcao(array("date_to_php","date_to_php",NULL,NULL,"date_to_php",NULL,NULL,"formataMoeda"));
        $relatorio->set_align(array("center"));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(FALSE);
        #$relatorio->set_bordaInterna(TRUE);
        $relatorio->set_subTotal(FALSE);
        $relatorio->set_totalRegistro(TRUE);
        $relatorio->set_dataImpressao(FALSE);
        $relatorio->set_cabecalhoRelatorio(FALSE);
        $relatorio->set_menuRelatorio(FALSE);
        #$relatorio->set_linhaNomeColuna(FALSE);
        $relatorio->set_log(FALSE);
        $relatorio->show();
    }
    
    # Data da Impressão
    p('Emitido em: '.date('d/m/Y - H:i:s')." (".$idUsuario.")",'pRelatorioDataImpressao');
    
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}