<?php
/**
 * Sistema GRH
 * 
 * Ficha Cadastral
 *   
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("../grhSistema/_config.php");

# Verifica qual será a matrícula a ser exibida
if(is_null($idServidorPesquisado))
    $idFicha = $idUsuario;
else
    $idFicha = $idServidorPesquisado;

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

if($acesso)
{    
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
                      tbperfil.nome,
                      tbsituacao.situacao 
                 FROM tbservidor left join tbpessoa on (tbservidor.idPessoa = tbpessoa.idPessoa)
                                    left join tbperfil on (tbservidor.idPerfil = tbperfil.idPerfil)
                                    left join tbsituacao on (tbservidor.situacao = tbsituacao.idSituacao)
                WHERE tbservidor.idServidor = '.$idFicha;

    $result = $pessoal->select($select);   

    $relatorio = new Relatorio('relatorioFichaCadastral');
    $relatorio->set_titulo('Ficha Cadastral');
    $relatorio->set_label(array('IdFuncional','Matrícula','Nome','Perfil','Situação'));
    $relatorio->set_width(array(15,10,40,15,20));
    $relatorio->set_align(array('center'));
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    #$relatorio->set_botaoVoltar(false);
    $relatorio->set_zebrado(false);
    #$relatorio->set_bordaInterna(true);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_linhaNomeColuna(false);
    $relatorio->set_brHr(0);
    $relatorio->set_formCampos(array(
              array ('nome' => 'contatos',
                     'label' => 'Contatos',
                     'tipo' => 'simnao',
                     'valor' => $postContatos,
                     'size' => 5,
                     'title' => 'Exibe os Contatos do Servidor (Telefones, Emails, etc)',
                     'onChange' => 'formPadrao.submit();',
                     'fieldset' => 'Escolha a Área a ser Exibida na Ficha Cadastral',
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
                     'formFieldset' => 'fecha',
                     'col' => 3,
                     'linha' => 3)));

    $relatorio->set_formFocus('contatos');		
    $relatorio->set_formLink('?');
    $relatorio->set_logServidor($idFicha);
    $relatorio->show();        

    /*
     * Dados Funcionais
     */

    $fieldset = new Fieldset('Dados Funcionais','fieldsetRelatorio');
    $fieldset->abre();

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
    #$relatorio->set_titulo(null);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Data Admissão','Cargo','Concurso','Data Demissão','Motivo'));
    $relatorio->set_width(array(12,30,20,12,26));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array("date_to_php",null,null,"date_to_php"));
    $relatorio->set_classe(array(null,"Pessoal"));
    $relatorio->set_metodo(array(null,"get_Cargo"));
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_zebrado(false);
    #$relatorio->set_bordaInterna(true);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    #$relatorio->set_linhaNomeColuna(false);
    $relatorio->set_log(false);    
    $relatorio->show();

    $fieldset->fecha();

    /*
     * Dados Financeiros
     */

    $fieldset = new Fieldset('Dados Financeiros','fieldsetRelatorio');
    $fieldset->abre();        

    # pega os valores
    $salarioBase = $pessoal->get_salarioBase($idFicha);                              // salário base
    $trienio = ($salarioBase * ($pessoal->get_trienioPercentual($idFicha)))/100;     // triênio
    $comissao = $pessoal->get_salarioCargoComissao($idFicha);                        // cargo em comissão
    $gratificacao = $pessoal->get_gratificacao($idFicha);                            // gratificação especial
    $total = $salarioBase + $trienio + $comissao + $gratificacao;
    $conteudo = array(array($salarioBase,$trienio,$comissao,$gratificacao,$total));

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(null);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Salário Base','Triênio','Cargo em Comissão','Gratificação Especial','Total'));
    $relatorio->set_width(array(20,20,20,20,20));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array('formataMoeda','formataMoeda','formataMoeda','formataMoeda','formataMoeda'));
    $relatorio->set_conteudo($conteudo);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_zebrado(false);
    #$relatorio->set_bordaInterna(true);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    #$relatorio->set_linhaNomeColuna(false);
    $relatorio->set_log(false);
    $relatorio->show();

    $fieldset->fecha();

    /*
     * Dados dos Cedidos
     */

    # Pega o idPerfil da matricula
    $idPerfil = $pessoal->get_idPerfil($idFicha);

    # Verifica se é Cedido
    if ($idPerfil == '2')
    {
        $fieldset = new Fieldset('Dados dos Cedidos','fieldsetRelatorio');
        $fieldset->abre();   

        $select = 'SELECT orgaoOrigem,
                          matExterna,
                          onus,
                          salario,
                          processo,
                          dtPublicacao,
                          pgPublicacao
                     FROM tbcedido
                    WHERE idServidor = '.$idFicha;

        $result = $pessoal->select($select);    

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Órgão de Origem','Matrícula Externa','Cedido com Ônus','Salário','Processo de Cessão','Publicação','Pag.'));
        $relatorio->set_width(array(15,15,15,10,20,15,5));    
        $relatorio->set_align(array('cener'));
        $relatorio->set_funcao(array(null,null,null,'formataMoeda',null,"date_to_php"));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(false);
        $relatorio->set_zebrado(false);
        #$relatorio->set_bordaInterna(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);    
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();

        $fieldset->fecha();
    }

    /*
     * Dados Pessoais
     */

    # Pega o idPessoa
    $idPessoa = $pessoal->get_idPessoa($idFicha);		

    $fieldset = new Fieldset('Dados Pessoais','fieldsetRelatorio');
    $fieldset->abre();

    $select = 'SELECT dtNasc,
                        nacionalidade,
                        naturalidade,
                        tbestciv.estCiv,
                        sexo
                    FROM tbpessoa join tbestciv on (tbpessoa.estCiv = tbestciv.idEstCiv)
                WHERE idPessoa = '.$idPessoa;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(null);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Nascimento','Nacionalidade','Naturalidade','Estado Civil','Sexo'));
    $relatorio->set_width(array(20,20,20,20,20));
    $relatorio->set_funcao(array("date_to_php"));
    $relatorio->set_align(array('center'));
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_zebrado(false);
    #$relatorio->set_bordaInterna(true);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    #$relatorio->set_linhaNomeColuna(false);
    $relatorio->set_log(false);
    $relatorio->show();

    $fieldset->fecha();

    /*
     * Filiação
     */

    $fieldset = new Fieldset('Filiação','fieldsetRelatorio');
    $fieldset->abre();

    $select = 'SELECT nomePai,
                        nomeMae 
                    FROM tbpessoa
                WHERE idPessoa = '.$idPessoa;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(null);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Nome do Pai','Nome da Mãe'));
    $relatorio->set_width(array(50,50));
    $relatorio->set_align(array('center'));
    #$relatorio->set_funcao($funcao);
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_zebrado(false);
    #$relatorio->set_bordaInterna(true);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    #$relatorio->set_linhaNomeColuna(false);
    $relatorio->set_log(false);
    $relatorio->show();

    $fieldset->fecha();

    /*
     * Documentação
     */

    $fieldset = new Fieldset('Documentação','fieldsetRelatorio');
    $fieldset->abre();

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
    #$relatorio->set_titulo(null);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('CPF','Identidade - Órgão - Emissão','PisPasep','Título de Eleitor - Zona - Seção'));
    $relatorio->set_width(array(20,30,20,30));
    $relatorio->set_align(array('center'));
    #$relatorio->set_funcao($funcao);
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_zebrado(false);
    #$relatorio->set_bordaInterna(true);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    #$relatorio->set_linhaNomeColuna(false);
    $relatorio->set_log(false);
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
    #$relatorio->set_titulo(null);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Carteira Motorista','Vencimento','Conselho de Classe','Registro','Reservista'));
    $relatorio->set_width(array(20,20,20,20,20));
    $relatorio->set_align(array('center'));
    $relatorio->set_funcao(array(null,"date_to_php"));
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_zebrado(false);
    #$relatorio->set_bordaInterna(true);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    #$relatorio->set_linhaNomeColuna(false);
    $relatorio->set_log(false);
    $relatorio->show();

    $fieldset->fecha();

    /*
     * Endereço
     */

    $fieldset = new Fieldset('Endereço','fieldsetRelatorio');      
    $fieldset->abre();

    $select = 'SELECT CONCAT(IF(endereco is NULL," ",endereco)," ",
                             IF(complemento is NULL,"",complemento)," - ",
                             IF(bairro is NULL,"",bairro)," - ",
                             IF(cidade is NULL,"",cidade)," - ",
                             IF(UF is NULL,"",UF)),
                      cep 
                 FROM tbpessoa
                WHERE idPessoa = '.$idPessoa;

    $result = $pessoal->select($select);

    $relatorio = new Relatorio('relatorioFichaCadastral');
    #$relatorio->set_titulo(null);
    #$relatorio->set_subtitulo($subtitulo);
    $relatorio->set_label(array('Endereço','Cep'));
    $relatorio->set_width(array(80,20));
    $relatorio->set_align(array('left','center'));
    #$relatorio->set_funcao($funcao);
    $relatorio->set_conteudo($result);
    #$relatorio->set_numGrupo(0);
    $relatorio->set_botaoVoltar(false);
    $relatorio->set_zebrado(false);
    #$relatorio->set_bordaInterna(true);
    $relatorio->set_subTotal(false);
    $relatorio->set_totalRegistro(false);
    $relatorio->set_dataImpressao(false);
    $relatorio->set_cabecalhoRelatorio(false);
    $relatorio->set_menuRelatorio(false);
    #$relatorio->set_linhaNomeColuna(false);
    $relatorio->set_log(false);
    $relatorio->show();

    $fieldset->fecha();

    /*
     * Contatos
     */

    if($postContatos)
    {
        $fieldset = new Fieldset('Contatos','fieldsetRelatorio');      
        $fieldset->abre();

        $select = 'SELECT tipo,
                            numero
                        FROM tbcontatos
                    WHERE idPessoa = '.$idPessoa;

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Tipo','Número'));
        $relatorio->set_width(array(50,50));
        $relatorio->set_align(array('left','left'));
        #$relatorio->set_funcao($funcao);
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(false);
        $relatorio->set_zebrado(true);
        #$relatorio->set_bordaInterna(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();

        $fieldset->fecha();
    }

    /*
     * Dependentes
     */

    if($postDependentes)
    {
        $fieldset = new Fieldset('Dependentes','fieldsetRelatorio');   
        $fieldset->abre();

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
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array("Nome","Nascimento","Parentesco","Sexo","Depend. no IR","Auxílio Creche","Término do Aux. Creche"));
        $relatorio->set_width(array(30,10,10,10,10,10,10));
        $relatorio->set_funcao(array(null,"date_to_php",null,null,null,null,"date_to_php"));
        $relatorio->set_align(array('left','center'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(false);
        $relatorio->set_zebrado(true);
        #$relatorio->set_bordaInterna(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();

        $fieldset->fecha();
    }

    /*
     * Formação
     */

    if($postFormacao)
    {
        $fieldset = new Fieldset('Formação','fieldsetRelatorio');      
        $fieldset->abre();

        $select = 'SELECT tbescolaridade.Escolaridade,
                            habilitacao,
                            instEnsino,
                            anoTerm
                        FROM tbformacao join tbescolaridade on (tbformacao.escolaridade = tbescolaridade.idEscolaridade)
                    WHERE idPessoa = '.$idPessoa.'
                    ORDER BY anoterm desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Nível','Curso','Instituição','Término'));
        $relatorio->set_width(array(20,35,35,10));
        $relatorio->set_align(array('left','left','left','left'));
        #$relatorio->set_funcao($funcao);
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(false);
        $relatorio->set_zebrado(true);
        #$relatorio->set_bordaInterna(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();

        $fieldset->fecha();
    }

    /*
     * Histórico de Lotações
     */

    if($postLotacao)
    {
        $fieldset = new Fieldset('Histórico de Lotações','fieldsetRelatorio');
        $fieldset->abre();

        $select ='SELECT tbhistlot.data,
                         concat(tblotacao.UADM,"-",tblotacao.DIR,"-",tblotacao.GER) as lotacao,
                         tbhistlot.motivo
                    FROM tblotacao join tbhistlot on (tblotacao.idLotacao = tbhistlot.lotacao)
                   WHERE tbhistlot.idservidor = '.$idFicha.'
                ORDER BY tbhistlot.data desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Data','Lotação','Motivo'));
        $relatorio->set_width(array(20,40,40));
        $relatorio->set_funcao(array("date_to_php"));
        $relatorio->set_align(array('center','left','left'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(false);
        $relatorio->set_zebrado(true);
        #$relatorio->set_bordaInterna(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();

        $fieldset->fecha();
    }

    /*
     * Histórico de Cargo em Comissão
     */

    if($postCargo)
    {
        $fieldset = new Fieldset('Histórico de Cargos em Comissão','fieldsetRelatorio');
        $fieldset->abre();

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
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Cargo','Valor','Nomeação','Processo','Exoneração','Processo'));
        $relatorio->set_width(array(20,10,15,20,15,20));
        $relatorio->set_funcao(array(null,'formataMoeda','date_to_php',null,'date_to_php'));
        $relatorio->set_align(array('left','left','left','left','left','left'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(false);
        $relatorio->set_zebrado(true);
        #$relatorio->set_bordaInterna(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();

        $fieldset->fecha();
    }

    /*
     * Histórico de Progressão e Enquadramento 
     */

    if($postProgressao)
    {
        $fieldset = new Fieldset('Histórico de Progressões e Enquadramentos','fieldsetRelatorio');
        $fieldset->abre();

        $select ='SELECT tbprogressao.dtInicial,
                         tbtipoprogressao.nome,
                         CONCAT(tbclasse.faixa," - ",tbclasse.valor) as vv,
                         tbprogressao.numProcesso,
                         tbprogressao.dtPublicacao,
                         tbprogressao.pgPublicacao
                    FROM tbprogressao JOIN tbtipoprogressao ON (tbprogressao.idTpProgressao = tbtipoprogressao.idTpProgressao)
                                      JOIN tbclasse ON (tbprogressao.idClasse = tbclasse.idClasse)
                    WHERE idServidor = '.$idFicha.'
                 ORDER BY tbprogressao.dtInicial desc, vv desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Data Inicial','Tipo','Valor','Processo','DOERJ','Pág.'));
        $relatorio->set_width(array(10,25,20,20,10,5));
        $relatorio->set_funcao(array('date_to_php',null,null,null,'date_to_php'));
        $relatorio->set_align(array('center','left','center'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(false);
        $relatorio->set_zebrado(true);
        #$relatorio->set_bordaInterna(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();

        $fieldset->fecha();
    }

    /*
     * Histórico de Triênio
     */

    if($postTrienio)
    {
        $fieldset = new Fieldset('Histórico de Triênio','fieldsetRelatorio');
        $fieldset->abre();

        $select = 'SELECT dtInicial,
                          percentual,
                          numProcesso,
                          dtPublicacao,
                          pgPublicacao
                     FROM tbtrienio
                    WHERE idServidor = '.$idFicha.'
                    ORDER BY dtInicial desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Data Inicial','Percentual (%)','Processo','DOERJ','Pág.'));
        $relatorio->set_width(array(20,20,20,20,20));
        $relatorio->set_funcao(array('date_to_php',null,null,'date_to_php'));
        $relatorio->set_align(array('center'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(false);
        $relatorio->set_zebrado(true);
        #$relatorio->set_bordaInterna(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();

        $fieldset->fecha();
    }

    /*
     * Histórico de Gratificação Especial
     */

    if($postGratificacao)
    {
        $fieldset = new Fieldset('Histórico de Gratificação Especial','fieldsetRelatorio');
        $fieldset->abre();

        $select = 'SELECT dtInicial,
                          dtFinal,
                          valor,
                          processo
                     FROM tbgratificacao
                    WHERE idServidor = '.$idFicha.'
                    ORDER BY dtInicial desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Data Inicial','Data Final','Valor','Processo'));
        $relatorio->set_width(array(25,25,25,25));
        $relatorio->set_funcao(array('date_to_php','date_to_php','formataMoeda'));
        $relatorio->set_align(array('left','left','left','left'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(false);
        $relatorio->set_zebrado(true);
        #$relatorio->set_bordaInterna(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();

        $fieldset->fecha();
    }

    /*
     * Histórico de Férias
     */
    
    if($postFerias)
    {
        $fieldset = new Fieldset('Histórico de Férias','fieldsetRelatorio');
        $fieldset->abre();

        $select = 'SELECT anoExercicio,
                          status,
                          dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1),
                          documento,
                          folha
                     FROM tbferias
                    WHERE idServidor='.$idFicha.'
                    ORDER BY anoExercicio desc,dtInicial desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Exercício','Status','Data Inicial','Dias','Data Final','Documento 1/3','Folha'));
        $relatorio->set_width(array(10,10,15,10,15,20,20));
        $relatorio->set_funcao(array(null,null,'date_to_php',null,'date_to_php'));
        $relatorio->set_align(array('center'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(false);
        $relatorio->set_zebrado(true);
        #$relatorio->set_bordaInterna(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();

        $fieldset->fecha();
    }

    /*
     * Histórico de Afastamentos 
     */
    
    if($postLicenca)
    {
        $fieldset = new Fieldset('Histórico de Afastamentos, Faltas e Licenças','fieldsetRelatorio');
        $fieldset->abre();

        $select = 'SELECT tbtipolicenca.nome,
                          tblicenca.dtInicial,
                          tblicenca.numdias,
                          ADDDATE(tblicenca.dtInicial,tblicenca.numDias-1),
                          tblicenca.dtPericia,
                          tblicenca.num_Bim,
                          tblicenca.processo,
                          tblicenca.dtPublicacao,
                          tblicenca.pgPublicacao
                     FROM tblicenca JOIN tbtipolicenca on (tblicenca.idTpLicenca = tbtipolicenca.idTpLicenca)
                    WHERE idServidor='.$idFicha.'
                    ORDER BY tblicenca.dtInicial desc';

        $result = $pessoal->select($select);

        $relatorio = new Relatorio('relatorioFichaCadastral');
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array('Tipo','Início','Dias','Término','Perícia','Bim','Processo','DOERJ','Pág.'));
        $relatorio->set_width(array(22,10,2,10,10,6,15,10,5));
        $relatorio->set_funcao(array(null,'date_to_php',null,'date_to_php','date_to_php',null,null,'date_to_php'));
        $relatorio->set_align(array('left','center'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(false);
        $relatorio->set_zebrado(true);
        #$relatorio->set_bordaInterna(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();

        $fieldset->fecha();
    }    
    
    /*
     * Tempo de Serviço Averbado
     */

    if($postAverbacao)
    {
        $fieldset = new Fieldset('Tempo de Serviço Averbado','fieldsetRelatorio');               
        $fieldset->abre();

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
                        CASE cargo
                        WHEN 1 THEN "Professor"
                        WHEN 2 THEN "Outros"
                        END,
                        dtPublicacao,
                        pgPublicacao,
                        processo
                FROM tbaverbacao
                    WHERE idServidor='.$idFicha.'
                    ORDER BY dtInicial desc';

        $result = $pessoal->select($select);
        $relatorio = new Relatorio();
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);        
        $relatorio->set_label(array("Data Inicial","Data Final","Dias","Empresa","Tipo","Regime","Cargo","Publicação","Pag.","Processo"));
        $relatorio->set_width(array(10,10,5,20,8,10,8,10,3,15));
        $relatorio->set_funcao(array("date_to_php","date_to_php",null,null,null,null,null,"date_to_php"));
        $relatorio->set_align(array('left','left','left','left','left','left','left','left','left','Left'));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(false);
        $relatorio->set_zebrado(true);
        #$relatorio->set_bordaInterna(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->set_log(false);
        $relatorio->show();

        $fieldset->fecha();
    } 
    
    /*
     * Histórico de Diária
     */

    if($postDiaria)
    {
        $fieldset = new Fieldset('Histórico de Diária','fieldsetRelatorio');
        $fieldset->abre();

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
        #$relatorio->set_titulo(null);
        #$relatorio->set_subtitulo($subtitulo);
        $relatorio->set_label(array("Saída","Chegada","CI","Processo","Data","Origem","Destino","Valor"));
        $relatorio->set_width(array(10,10,10,10,10,20,20,10));
        $relatorio->set_funcao(array("date_to_php","date_to_php",null,null,"date_to_php",null,null,"formataMoeda"));
        $relatorio->set_align(array("center"));
        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(0);
        $relatorio->set_botaoVoltar(false);
        $relatorio->set_zebrado(true);
        #$relatorio->set_bordaInterna(true);
        $relatorio->set_subTotal(false);
        $relatorio->set_totalRegistro(true);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        #$relatorio->set_linhaNomeColuna(false);
        $relatorio->set_log(false);
        $relatorio->show();

        $fieldset->fecha();
    }
    
    # Data da Impressão
    p('Emitido em: '.date('d/m/Y - H:i:s')." (".$idUsuario.")",'pRelatorioDataImpressao');
    
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}