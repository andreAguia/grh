<?php
/**
 * Área de Recadastramento
 *  
 * By Alat
 */

# Reservado para o servidor logado
$idUsuario = NULL;

# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){   
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase');
    
    # Verifica se veio menu grh e registra o acesso no log
    $origem = get('origem',FALSE);
    if($origem){
        # Grava no log a atividade
        $atividade = "Visualizou a área de Recadastramento";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario,$data,$atividade,NULL,NULL,7);
    }
    
    # pega o id (se tiver)
    $id = soNumeros(get('id'));
    
    # Pega os parâmetros
    $parametroNomeMat = post('parametroNomeMat',get_session('parametroNomeMat'));
    $parametroLotacao = post('parametroLotacao',get_session('parametroLotacao'));
    $parametroCargo = post('parametroCargo',get_session('parametroCargo'));
        
    # Joga os parâmetros par as sessions    
    set_session('parametroNomeMat',$parametroNomeMat);
    set_session('parametroLotacao',$parametroLotacao);
    set_session('parametroCargo',$parametroCargo);
    set_session('areaRecadastramento',FALSE);
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    AreaServidor::cabecalho();
    
    $grid = new Grid();
    $grid->abreColuna(12);      
            
################################################################
    
    switch ($fase){
        case "" : 
            br(4);
            aguarde();
            br();
            
            # Limita a tela
            $grid1 = new Grid("center");
            $grid1->abreColuna(5);
                p("Aguarde...","center");
            $grid1->fechaColuna();
            $grid1->fechaGrid();

            loadPage('?fase=exibeLista');
            break;
        
################################################################
        
        case "exibeLista" :
            # Botao voltar
            botaoVoltar("grh.php");
    
            # Título
            titulo("Área de Recadastramento"); 
            
            # Formulário de Pesquisa
            $form = new Form('?');

            $controle = new Input('parametroNomeMat','texto','Nome, Matrícula ou id:',1);
            $controle->set_size(100);
            $controle->set_title('Nome do servidor');
            $controle->set_valor($parametroNomeMat);
            $controle->set_autofocus(TRUE);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(3);
            $form->add_item($controle);

            # Lotação
            $result = $pessoal->select('(SELECT idlotacao, concat(IFNULL(tblotacao.DIR,"")," - ",IFNULL(tblotacao.GER,"")," - ",IFNULL(tblotacao.nome,"")) lotacao
                                                      FROM tblotacao
                                                     WHERE ativo) UNION (SELECT distinct DIR, DIR
                                                      FROM tblotacao
                                                     WHERE ativo)
                                                  ORDER BY 2');
            array_unshift($result,array('*','-- Todos --'));

            $controle = new Input('parametroLotacao','combo','Lotação:',1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Lotação');
            $controle->set_array($result);
            $controle->set_valor($parametroLotacao);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(5);
            $form->add_item($controle);
            
            # Cargo
            $result = $pessoal->select('SELECT distinct tipo,tipo FROM tbtipocargo ORDER BY 1');
            array_unshift($result,array('*','-- Todos --'));

            $controle = new Input('parametroCargo','combo','Cargo:',1);
            $controle->set_size(30);
            $controle->set_title('Filtra por Tipo de Cargo');
            $controle->set_array($result);
            $controle->set_valor($parametroCargo);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
            $form->add_item($controle);
            
            $form->show();
            
            ###
            
            # Monta o select
            $select ='SELECT tbservidor.idFuncional,
                             tbpessoa.nome,
                             tbservidor.idServidor,
                             tbservidor.idServidor,
                             tbrecadastramento.dataAtualizacao,
                             tbrecadastramento.idUsuario,
                             tbservidor.idServidor,
                             tbservidor.idServidor
                        FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                        LEFT JOIN tbrecadastramento USING (idServidor)
                                        LEFT JOIN tbperfil USING (idPerfil)
                                        JOIN tbhistlot USING (idServidor)
                                        JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
                                        JOIN tbcargo USING (idCargo)
                                        JOIN tbtipocargo USING (idTipoCargo)
                      WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                        AND tbservidor.situacao = 1';
                      
            # Matrícula, nome ou id
            if(!is_null($parametroNomeMat)){
                if(is_numeric($parametroNomeMat)){
                    $select .= ' AND ((';
                }else{
                    $select .= ' AND (';
                }

                $select .= 'tbpessoa.nome LIKE "%'.$parametroNomeMat.'%")';

                if(is_numeric($parametroNomeMat)){
                    $select .= ' OR (tbservidor.matricula LIKE "%'.$parametroNomeMat.'%")
                                 OR (tbservidor.idfuncional LIKE "%'.$parametroNomeMat.'%"))';        
                }
            }
            
            # Lotação
            if(($parametroLotacao <> "*") AND ($parametroLotacao <> "")){
                if(is_numeric($parametroLotacao)){
                    $select .= ' AND (tblotacao.idlotacao = "'.$parametroLotacao.'")';
                }else{ # senão é uma diretoria genérica
                    $select .= ' AND (tblotacao.DIR = "'.$parametroLotacao.'")';
                }
            }
            
            # Tipo de Cargo
            if(($parametroCargo <> "*") AND ($parametroCargo <> "")){
                $select .= ' AND tbtipocargo.tipo = "'.$parametroCargo.'"';
            }

            $select .= ' ORDER BY tbpessoa.nome';
            
            #echo $select;

            $result = $pessoal->select($select);
            
            $grid2 = new Grid();
            
            ######################################################
            
            # Área Lateral
            $grid2->abreColuna(3);
            
            # Resumo
            $resumo = array();
            $totalServidores = $pessoal->count($select);
            
            # Calcula quantos atualizaram
            $select2 = "select idRecadastramento from tbrecadastramento";
            
            $atualizados = $pessoal->count($select2);
            
            # Calcula quantos servidores existem
            $select9 = "SELECT idServidor FROM tbservidor WHERE situacao = 1";
            $totalServidores = $pessoal->count($select9);
            
            $resumo[] = array("Servidores",$totalServidores);
            $resumo[] = array("Recadastrados",$atualizados);
            $faltam = $totalServidores - $atualizados;
            $resumo[] = array("Faltam",$faltam);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Descrição","Nº de Servidores"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_align(array("center"));
            $tabela->set_titulo("Resumo Geral");
            #$tabela->set_rodape("Total de Servidores: ".$totalServidores3);
            $tabela->show();
            
            # Professores
            
            # Calcula quantos professores existem
            $select3 = "SELECT idServidor FROM tbservidor JOIN tbcargo USING (idCargo) WHERE situacao = 1 AND (idTipoCargo = 1 OR idTipoCargo = 2)";
            $numProfessores = $pessoal->count($select3);
            
            # Calcula quantos atualizaram
            $select4 = "SELECT idRecadastramento FROM tbrecadastramento LEFT JOiN tbservidor USING (idServidor) JOIN tbcargo USING (idCargo) WHERE situacao = 1 AND (idTipoCargo = 1 OR idTipoCargo = 2)";
            $atualizados = $pessoal->count($select4);
                        
            $resumo = array();
            
            $resumo[] = array("Professores",$numProfessores);
            $resumo[] = array("Recadastrados",$atualizados);
            $faltam = $numProfessores - $atualizados;
            $resumo[] = array("Faltam",$faltam);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Descrição","Nº de Servidores"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_align(array("center"));
            $tabela->set_titulo("Professores");
            #$tabela->set_rodape("Total de Servidores: ".$totalServidores3);
            $tabela->show();
            
            # Sisgem
            
            # Calcula quantos realizaram
            $select5 = "SELECT idRecadastramento FROM tbrecadastramento LEFT JOiN tbservidor USING (idServidor) JOIN tbcargo USING (idCargo) WHERE situacao = 1 AND (idTipoCargo = 1 OR idTipoCargo = 2) AND sisgen";
            $realizaram = $pessoal->count($select5);
            
            # Calcula quantos nao realizaram
            $select6 = "SELECT idRecadastramento FROM tbrecadastramento LEFT JOiN tbservidor USING (idServidor) JOIN tbcargo USING (idCargo) WHERE situacao = 1 AND (idTipoCargo = 1 OR idTipoCargo = 2) AND NOT sisgen";
            $naoRealizaram = $pessoal->count($select6);
                        
            $resumo = array();
            
            $resumo[] = array("Realizaram",$realizaram);
            $resumo[] = array("Nao Realizaram",$naoRealizaram);
            $total = $realizaram + $naoRealizaram;
            $resumo[] = array("Total",$total);

            # Monta a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($resumo);
            $tabela->set_label(array("Descrição","Nº de Servidores"));
            $tabela->set_totalRegistro(FALSE);
            $tabela->set_align(array("center"));
            $tabela->set_titulo("Sisgen");
            #$tabela->set_rodape("Total de Servidores: ".$totalServidores3);
            $tabela->show();
            
            # Relatórios
            $menu = new Menu();
            $menu->add_item('titulo','Relatórios');
            $menu->add_item('titulo1','por Lotaçao');
            $menu->add_item('linkWindow','Servidores Recadastrados','../grhRelatorios/recadastramentoLotacao.php');
            $menu->add_item('linkWindow','Servidores que Faltam Recadastrar','../grhRelatorios/recadastramentoFaltamLotacao.php');
            $menu->add_item('titulo1','por Cargo');
            $menu->add_item('linkWindow','Servidores Recadastrados','../grhRelatorios/recadastramentoCargo.php');
            $menu->add_item('linkWindow','Servidores que Faltam Recadastrar','../grhRelatorios/recadastramentoFaltamCargo.php');
            $menu->add_item('titulo1','por Sisgen (Docentes)');
            $menu->add_item('linkWindow','Realizou Sisgen','../grhRelatorios/recadastramentoSisgen.php?sisgen=1');
            $menu->add_item('linkWindow','Nao Realizou Sisgem','../grhRelatorios/recadastramentoSisgen.php?sisgen=0');
            $menu->show();
            
            $grid2->fechaColuna();
            
            ######################################################
            
            $grid2->abreColuna(9);
            
            $tabela = new Tabela();
            $tabela->set_titulo('Servidores');
            $tabela->set_label(array('IdFuncional','Nome','Cargo','Lotação','Atualizado em:','Usuario','Editar','Formaçao'));
            #$relatorio->set_width(array(10,30,30,0,10,10,10));
            $tabela->set_align(array("center","left","left","left"));
            $tabela->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));

            $tabela->set_classe(array(NULL,NULL,"pessoal","pessoal",NULL,"Intra"));
            $tabela->set_metodo(array(NULL,NULL,"get_Cargo","get_Lotacao",NULL,"get_usuario"));
            
            if(!is_null($parametroNomeMat)){
                $tabela->set_textoRessaltado($parametroNomeMat);
            }

            # Botão de exibição dos servidores com permissão a essa regra
            $botao1 = new BotaoGrafico();
            $botao1->set_label('');
            $botao1->set_title('Recadastrar Servidor');
            $botao1->set_url('?fase=editar&id=');
            $botao1->set_image(PASTA_FIGURAS.'bullet_edit.png',20,20);
            
            # Botão para ao cadastro de servidor
            $botao2 = new BotaoGrafico();
            $botao2->set_label('');
            $botao2->set_title('Cadastro Servidor');
            $botao2->set_url('?fase=editaServidor&id=');
            $botao2->set_image(PASTA_FIGURAS.'diploma.jpg',20,20);
            
            # Coloca o objeto link na tabela			
            $tabela->set_idCampo('idServidor');
            $tabela->set_link(array("","","","","","",$botao1,$botao2));

            $tabela->set_conteudo($result);
            $tabela->show();
            
            $grid2->fechaColuna();
            $grid2->fechaGrid();
            break;
        
################################################################
        
        case "editar" :
            
            # Botao voltar
            botaoVoltar("?");
            
            # Dados do Servidor
            get_DadosServidor($id);
                
            # Titulo
            tituloTable("Recadastramento");
                                   
            # Monta o select
            $select ="SELECT tbpessoa.telResidencialDDD,
                             tbpessoa.telResidencial,
                             tbpessoa.telCelularDDD,
                             tbpessoa.telCelular,
                             tbpessoa.telRecadosDDD,
                             tbpessoa.telRecados,
                             tbpessoa.emailUenf,
                             tbpessoa.emailPessoal,
                             tbpessoa.endereco,
                             tbpessoa.bairro,
                             tbpessoa.idCidade,
                             tbpessoa.cep,
                             tbdocumentacao.cpf,
                             tbdocumentacao.identidade,
                             tbdocumentacao.orgaoId,
                             tbdocumentacao.dtId,
                             tbpessoa.estCiv,
                             tbpessoa.conjuge,
                             tbpessoa.nomePai,
                             tbpessoa.nomeMae,
                             tbrecadastramento.sisgen
                        FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                             JOIN tbdocumentacao USING (idPessoa)
                                             LEFT JOIN tbrecadastramento USING (idServidor)
                       WHERE tbservidor.idServidor = $id";

            $result = $pessoal->select($select,false);
            
            # Envia o valor original por session para produzir
            # o log de atividades na rotina de validaçao
            set_session('oldValue', $result);
            
            # Formuário exemplo de login
            $form = new Form('?fase=valida','Atualiza dados?');
            
            # Pega o tipo do cargo
            $tipoCargo = $pessoal->get_cargoTipo($id);
            
            if($tipoCargo == "Professor"){
                # SISGEN
                $controle = new Input('sisgen','combo','Realizou as atividades descritas no Anexo III?:',1);
                $controle->set_size(100);
                $controle->set_linha(1);
                $controle->set_array(array(array(1,"Realizei"),array(0,"Não Realizei"),array(NULL,"---")));
                $controle->set_valor($result['sisgen']);    
                $controle->set_col(4);
                $controle->set_autofocus(TRUE); 
                $controle->set_fieldset("Declaração de Conformidade com o SISGEN");
                $form->add_item($controle);
            }
            
            # CPF
            $controle = new Input('cpf','cpf','CPF:',1);
            $controle->set_size(20);
            $controle->set_linha(2);
            $controle->set_valor($result['cpf']);
            $controle->set_col(3);
            $controle->set_autofocus(TRUE); 
            $controle->set_fieldset("Documentos");
            $form->add_item($controle);
            
            # Identidade
            $controle = new Input('identidade','texto','Identidade:',1);
            $controle->set_size(20);
            $controle->set_linha(2);
            $controle->set_valor($result['identidade']);
            $controle->set_col(3);
            $form->add_item($controle);
            
            # Identidade Órgão
            $controle = new Input('orgaoId','texto','Órgão:',1);
            $controle->set_size(20);
            $controle->set_linha(2);
            $controle->set_valor($result['orgaoId']);
            $controle->set_col(3);
            $form->add_item($controle);
            
            # Identidade Data de Emissão
            $controle = new Input('dtId','data','Data de Emissão:',1);
            $controle->set_size(15);
            $controle->set_linha(2);
            $controle->set_valor($result['dtId']);
            $controle->set_col(3);
            $form->add_item($controle);
            
            # Endereço
            $controle = new Input('endereco','texto','Endereço do Servidor:',1);
            $controle->set_size(150);
            $controle->set_linha(3);
            $controle->set_valor(ucwords(mb_strtolower($result['endereco'])));
            $controle->set_col(12);
            $controle->set_fieldset("Endereço");
            $form->add_item($controle);
            
            # Bairro
            $controle = new Input('bairro','texto','Bairro:',1);
            $controle->set_size(50);
            $controle->set_linha(4);
            $controle->set_valor(ucwords(mb_strtolower($result['bairro'])));
            $controle->set_col(5);
            $form->add_item($controle);
            
            # Pega os dados da combo de cidade
            $cidade = $pessoal->select('SELECT idCidade,
                                               CONCAT(tbcidade.nome," (",tbestado.uf,")")
                                          FROM tbcidade JOIN tbestado USING (idEstado)
                                      ORDER BY proximidade,tbestado.uf,tbcidade.nome');
            array_unshift($cidade, array(NULL,NULL)); # Adiciona o valor de nulo
            
            # Cidade
            $controle = new Input('idCidade','combo','Cidade:',1);
            $controle->set_size(50);
            $controle->set_linha(4);
            $controle->set_array($cidade);
            $controle->set_valor($result['idCidade']);
            $controle->set_col(5);
            $form->add_item($controle);
            
            # Cep
            $controle = new Input('cep','cep','Cep:',1);
            $controle->set_size(10);
            $controle->set_linha(4);
            $controle->set_valor($result['cep']);
            $controle->set_col(2);
            $form->add_item($controle);
            
            # DDD
            $controle = new Input('telResidencialDDD','texto','DDD:',1);
            $controle->set_size(2);
            $controle->set_linha(5);
            $controle->set_valor($result['telResidencialDDD']);
            $controle->set_col(1);
            $controle->set_fieldset("Telefones");
            $form->add_item($controle);

            # Telefone Residencial
            $controle = new Input('telResidencial','texto','Telefone Residencial:',1);
            $controle->set_size(30);
            $controle->set_linha(5);
            $controle->set_valor($result['telResidencial']);
            $controle->set_col(3);
            $form->add_item($controle);
            
            # DDD
            $controle = new Input('telCelularDDD','texto','DDD:',1);
            $controle->set_size(2);
            $controle->set_linha(5);
            $controle->set_valor($result['telCelularDDD']);
            $controle->set_col(1);
            $form->add_item($controle);
            
            # Telefone Celular
            $controle = new Input('telCelular','texto','Telefone Celular:',1);
            $controle->set_size(30);
            $controle->set_linha(5);
            $controle->set_valor($result['telCelular']);
            $controle->set_col(3);
            $form->add_item($controle);
            
            # DDD
            $controle = new Input('telRecadosDDD','texto','DDD:',1);
            $controle->set_size(2);
            $controle->set_linha(5);
            $controle->set_valor($result['telRecadosDDD']);
            $controle->set_col(1);
            $form->add_item($controle);
            
            # Outro telefone para recado
            $controle = new Input('telRecados','texto','Outro telefone para recado:',1);
            $controle->set_size(30);
            $controle->set_linha(5);
            $controle->set_valor($result['telRecados']);
            $controle->set_col(3);            
            $form->add_item($controle);
            
            # Email institucional da Uenf
            $controle = new Input('emailUenf','texto','E-mail institucional da Uenf:',1);
            $controle->set_size(100);
            $controle->set_linha(6);
            $controle->set_valor(strtolower($result['emailUenf']));
            $controle->set_col(6);
            $controle->set_fieldset("Email");
            $form->add_item($controle);
            
            # Email Pessoal
            $controle = new Input('emailPessoal','texto','E-mail Pessoal:',1);
            $controle->set_size(100);
            $controle->set_linha(6);
            $controle->set_valor(strtolower($result['emailPessoal']));
            $controle->set_col(6);            
            $form->add_item($controle);
            
            # Pega os dados da combo de estado civil
            $estadoCivil = $pessoal->select('SELECT idestCiv,estciv FROM tbestciv ORDER BY estciv');
            array_unshift($estadoCivil, array(NULL,NULL)); # Adiciona o valor de nulo
            
            # Estado Civil
            $controle = new Input('estCiv','combo','Estado Civil:',1);
            $controle->set_size(15);
            $controle->set_linha(7);
            $controle->set_valor($result['estCiv']);
            $controle->set_array($estadoCivil);
            $controle->set_col(4);
            $controle->set_fieldset("Estado Civil");
            $form->add_item($controle);
            
            # Conjuge
            $controle = new Input('conjuge','texto','Nome do Conjuge:',1);
            $controle->set_size(100);
            $controle->set_linha(7);
            $controle->set_valor(ucwords(mb_strtolower($result['conjuge'])));
            $controle->set_col(6);
            $form->add_item($controle);
            
            # Nome do Pai
            $controle = new Input('nomePai','texto','Nome do Pai:',1);
            $controle->set_size(50);
            $controle->set_linha(8);
            $controle->set_valor(ucwords(mb_strtolower($result['nomePai'])));
            $controle->set_col(6);
            $controle->set_fieldset("Filiaçao");
            $form->add_item($controle);
            
            # Nome da Mae
            $controle = new Input('nomeMae','texto','Nome do Mãe:',1);
            $controle->set_size(50);
            $controle->set_linha(8);
            $controle->set_valor(ucwords(mb_strtolower($result['nomeMae'])));
            $controle->set_col(6);
            $form->add_item($controle);
            
            # idServidor
            $controle = new Input('idServidor','hidden','idServidor:',1);
            $controle->set_size(10);
            $controle->set_linha(8);
            $controle->set_valor($id);
            $controle->set_col(3);
            $controle->set_fieldset("fecha");
            $form->add_item($controle);
            
            # submit
            $controle = new Input('submit','submit');
            $controle->set_valor('Atualizar');
            $controle->set_linha(8);
            $controle->set_tabIndex(3);
            $controle->set_accessKey('E');
            $form->add_item($controle);

            $form->show();
            break;
        
     ################################################################

        # Chama o menu do Servidor que se quer editar
        case "editaServidor" :
            set_session('idServidorPesquisado',$id);
            set_session('areaRecadastramento',TRUE);
            loadPage('servidorFormacao.php');
            break; 
    
    ################################################################
        
        case "valida" :
            # Pega os dados digitados
            $idServidor = post("idServidor");
            $sisgen = post("sisgen");
            $cpf = post("cpf");
            $identidade = post("identidade");
            $orgaoId = post("orgaoId");
            $dtId = post("dtId");
            $endereco = ucwords(mb_strtolower(post("endereco")));
            $bairro = ucwords(mb_strtolower(post("bairro")));
            $idCidade = post("idCidade");
            $cep = post("cep");
            $telResidencialDDD = post("telResidencialDDD");
            $telResidencial = post("telResidencial");
            $telCelularDDD = post("telCelularDDD");
            $telCelular = post("telCelular");
            $telRecadosDDD = post("telRecadosDDD");
            $telRecados = post("telRecados");
            $emailUenf = strtolower(post("emailUenf"));
            $emailPessoal = strtolower(post("emailPessoal"));
            $estCiv = post("estCiv");
            $conjuge = ucwords(mb_strtolower(post("conjuge")));
            $nomePai = ucwords(mb_strtolower(post("nomePai")));
            $nomeMae = ucwords(mb_strtolower(post("nomeMae")));
            $idPessoa = $pessoal->get_idPessoa($idServidor);
            
            $atividade = "Recadastramento de ".$pessoal->get_nome($idServidor);
                       
            # Variáveis dos erros
            $erro = 0;
            $msgErro = NULL;
           
            # dtId
            if(vazio($dtId)){
                $dtId = NULL;
            }            
            
            # Cpf
            if(vazio($cpf)){
                $msgErro.='O cpf nao pode estar em branco!\n';
                $erro = 1;
            }
            
            # Email Institucional
            if(!vazio($emailUenf)){                
                if(!filter_var($emailUenf, FILTER_VALIDATE_EMAIL)){
                    $msgErro.='E-mail Institucional Inválido!\n';
                    $erro = 1;
                }else{
                # Verifica se e realmente @uenf
                $pos = stripos($emailUenf, "@uenf");

                # se tem @uenf
                if($pos === false) {  
                    $msgErro.='O e-mail institucional nao é @uenf!\n';
                    $erro = 1; 
                }
            }
            }
            
            # Email Pessoal
            if(!vazio($emailPessoal)){                
                if(!filter_var($emailPessoal, FILTER_VALIDATE_EMAIL)){
                    $msgErro.='E-mail Pessoal Inválido!\n';
                    $erro = 1;
                }
            }
            
            if($erro == 0){
                # Data de Hoje
                $data = date("Y-m-d H:i:s");
                
                # Grava na tabela tbpessoa
                $campos = array('endereco','bairro','idCidade','cep','telResidencialDDD','telResidencial','telCelularDDD','telCelular','telRecadosDDD','telRecados','emailUenf','emailPessoal','estCiv','conjuge','nomePai','nomeMae');
                $valor = array($endereco,$bairro,$idCidade,$cep,$telResidencialDDD,$telResidencial,$telCelularDDD,$telCelular,$telRecadosDDD,$telRecados,$emailUenf,$emailPessoal,$estCiv,$conjuge,$nomePai,$nomeMae);
                $pessoal->gravar($campos,$valor,$idPessoa,"tbpessoa","idPessoa",FALSE);
                
                # Grava na tabela tbdocumentacao
                $campos = array('cpf','identidade','orgaoId','dtId');
                $valor = array($cpf,$identidade,$orgaoId,$dtId);
                $pessoal->gravar($campos,$valor,$idPessoa,"tbdocumentacao","idPessoa",FALSE);
                
                # Grava na tabela tbrecadastramento                
                $campos = array('idServidor','dataAtualizacao','idUsuario','sisgen');
                $valor = array($idServidor,$data,$idUsuario,$sisgen);
                
                # Antes de gravar verifica se já 
                # não existe um registro desse servidor
                $idRecadastramento = $pessoal->select('SELECT idRecadastramento FROM tbrecadastramento WHERE idServidor = '.$idServidor,FALSE);
                #echo $idRecadastramento[0];
                $pessoal->gravar($campos,$valor,$idRecadastramento[0],"tbrecadastramento","idRecadastramento",FALSE);
                
                # Grava no log a atividade                
                $tipoLog = 2;
                
                
                # Grava o log tbpessoa
                $intra->registraLog($idUsuario,$data,$atividade,"tbpessoa",$idPessoa,$tipoLog,$idServidor);
                loadPage("?");
            }else{
                alert($msgErro);
                back(1);
            }
            break;
        
################################################################
        
    }
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
