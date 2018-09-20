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
        
    # Joga os parâmetros par as sessions    
    set_session('parametroNomeMat',$parametroNomeMat);
    set_session('parametroLotacao',$parametroLotacao);
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Cabeçalho da Página
    AreaServidor::cabecalho();
    
    $grid = new Grid();
    $grid->abreColuna(12);
    
    # Título
    #titulo("Área de Recadastramento");       
            
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
            
            ###
            
            # Formulário de Pesquisa
            $form = new Form('?');

            $controle = new Input('parametroNomeMat','texto','Nome, Matrícula ou id:',1);
            $controle->set_size(100);
            $controle->set_title('Nome do servidor');
            $controle->set_valor($parametroNomeMat);
            $controle->set_autofocus(TRUE);
            $controle->set_onChange('formPadrao.submit();');
            $controle->set_linha(1);
            $controle->set_col(4);
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
                             tbservidor.idServidor
                        FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                        LEFT JOIN tbrecadastramento USING (idServidor)
                                        LEFT JOIN tbperfil USING (idPerfil)
                                        JOIN tbhistlot USING (idServidor)
                                        JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
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

            $select .= ' ORDER BY tbpessoa.nome';

            $result = $pessoal->select($select);
            
            $tabela = new Tabela();
            $tabela->set_titulo('Recadastramento');
            $tabela->set_label(array('IdFuncional','Nome','Cargo','Lotação','Atualizado em:','Usuario','Editar'));
            #$relatorio->set_width(array(10,30,30,0,10,10,10));
            $tabela->set_align(array("center","left","left","left"));
            $tabela->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));

            $tabela->set_classe(array(NULL,NULL,"pessoal","pessoal",NULL,"Intra"));
            $tabela->set_metodo(array(NULL,NULL,"get_Cargo","get_Lotacao",NULL,"get_usuario"));
            
            if(!is_null($parametroNomeMat)){
                $tabela->set_textoRessaltado($parametroNomeMat);
            }

            # Botão de exibição dos servidores com permissão a essa regra
            $botao = new BotaoGrafico();
            $botao->set_label('');
            $botao->set_title('Recadastrar Servidor');
            $botao->set_url('?fase=editar&id=');
            $botao->set_image(PASTA_FIGURAS.'bullet_edit.png',20,20);
            
            # Coloca o objeto link na tabela			
            $tabela->set_idCampo('idServidor');
            $tabela->set_link(array("","","","","","",$botao));

            $tabela->set_conteudo($result);
            $tabela->show();

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
            $select ="SELECT tbpessoa.telResidencial,
                             tbpessoa.telCelular,
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
                             tbpessoa.conjuge
                        FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                             JOIN tbdocumentacao USING (idPessoa)
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
                $controle->set_array(array("---","Realizei","Não Realizei"));
                $controle->set_valor("---");            
                $controle->set_col(4);
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
            $controle->set_valor($result['identidade']);
            $controle->set_col(3);
            $form->add_item($controle);
            
            # Endereço
            $controle = new Input('endereco','texto','Endereço do Servidor:',1);
            $controle->set_size(150);
            $controle->set_linha(3);
            $controle->set_valor(ucwords(strtolower($result['endereco'])));
            $controle->set_col(12);
            $controle->set_fieldset("Endereço");
            $form->add_item($controle);
            
            # Bairro
            $controle = new Input('bairro','texto','Bairro:',1);
            $controle->set_size(50);
            $controle->set_linha(4);
            $controle->set_valor(ucwords(strtolower($result['bairro'])));
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

            # Telefone Residencial
            $controle = new Input('telResidencial','texto','Telefone Residencial:',1);
            $controle->set_size(30);
            $controle->set_linha(5);
            $controle->set_valor($result['telResidencial']);
            $controle->set_col(4);
            $controle->set_fieldset("Telefones");
            $form->add_item($controle);
            
            # Telefone Celular
            $controle = new Input('telCelular','texto','Telefone Celular:',1);
            $controle->set_size(30);
            $controle->set_linha(5);
            $controle->set_valor($result['telCelular']);
            $controle->set_col(4);
            $form->add_item($controle);
            
            # Outro telefone para recado
            $controle = new Input('telRecados','texto','Outro telefone para recado:',1);
            $controle->set_size(30);
            $controle->set_linha(5);
            $controle->set_valor($result['telRecados']);
            $controle->set_col(4);            
            $form->add_item($controle);
            
            # Email institucional da Uenf
            $controle = new Input('emailUenf','texto','Email institucional da Uenf:',1);
            $controle->set_size(30);
            $controle->set_linha(6);
            $controle->set_valor(strtolower($result['emailUenf']));
            $controle->set_col(6);
            $controle->set_fieldset("Email");
            $form->add_item($controle);
            
            # Email Pessoal
            $controle = new Input('emailPessoal','texto','Email Pessoal:',1);
            $controle->set_size(30);
            $controle->set_linha(6);
            $controle->set_valor(strtolower($result['emailPessoal']));
            $controle->set_col(6);            
            $form->add_item($controle);
            
            # Conjuge
            $controle = new Input('conjuge','texto','Nome do Conjuge:',1);
            $controle->set_size(100);
            $controle->set_linha(7);
            $controle->set_valor(ucwords(strtolower($result['conjuge'])));
            $controle->set_col(6);
            $controle->set_fieldset("Certidão de Casamento");
            $form->add_item($controle);
            
            # idServidor
            $controle = new Input('idServidor','hidden','idServidor:',1);
            $controle->set_size(10);
            $controle->set_linha(8);
            $controle->set_valor($id);
            $controle->set_col(3);
            $form->add_item($controle);
            
            # submit
            $controle = new Input('submit','submit');
            $controle->set_valor('Atualizar');
            $controle->set_fieldset("fecha");
            $controle->set_linha(8);
            $controle->set_tabIndex(3);
            $controle->set_accessKey('E');
            $form->add_item($controle);

            $form->show();
            break;
        
        ###
        
        case "valida" :
            # Pega os dados digitados
            $idServidor = post("idServidor");
            $sisgen = post("sisgen");
            $cpf = post("cpf");
            $identidade = post("identidade");
            $orgaoId = post("orgaoId");
            $dtId = post("dtId");
            $endereco = post("endereco");
            $bairro = post("bairro");
            $idCidade = post("idCidade");
            $cep = post("cep");
            $telResidencial = post("telResidencial");
            $telCelular = post("telCelular");
            $telRecados = post("telRecados");
            $emailUenf = post("emailUenf");
            $emailPessoal = post("emailPessoal");
            $conjuge = post("conjuge");
            $idPessoa = $pessoal->get_idPessoa($idServidor);
            
            # Pega os valores antigos
            $oldValue = get_session('oldValue');
            $oldNomes = array_keys($oldValue);
            $atividade = "Recadastramento: ";
            
            
            # Percorre o array oldVersio comparando com o que
            # foi digitado para definir as diferenças
            #foreach ($oldNomes as $val){
            #    # Verifica se teve alteração
            ##    if($oldValue[$val] <> $$val){
            #        $atividade .= "[$val]: $oldValue[$val] -> $$val";
            #    }
            #}
                       
            # Variáveis dos erros
            $erro = 0;
            $msgErro = NULL;
            
            # Cpf
            if(vazio($cpf)){
                $msgErro.='O cpf nao pode estar em branco!\n';
                $erro = 1;
            }
            
            # Email Institucional
            if(!vazio($emailUenf)){                
                if(!filter_var($emailUenf, FILTER_VALIDATE_EMAIL)){
                    $msgErro.='Email Institucional Inválido!\n';
                    $erro = 1;
                }
            }
            
            # Email Pessoal
            if(!vazio($emailPessoal)){                
                if(!filter_var($emailPessoal, FILTER_VALIDATE_EMAIL)){
                    $msgErro.='Email Pessoal Inválido!\n';
                    $erro = 1;
                }
            }   
            
            if($erro == 0){
                # Data de Hoje
                $data = date("Y-m-d H:i:s");
                
                # Grava na tabela tbpessoa
                $campos = array('endereco','bairro','idCidade','cep','telResidencial','telCelular','telRecados','emailUenf','emailPessoal','conjuge');
                $valor = array($endereco,$bairro,$idCidade,$cep,$telResidencial,$telCelular,$telRecados,$emailUenf,$emailPessoal,$conjuge);
                $pessoal->gravar($campos,$valor,$idPessoa,"tbpessoa","idPessoa",FALSE);
                
                # Grava na tabela tbdocumentacao
                $campos = array('cpf','identidade','orgaoId','dtId');
                $valor = array($cpf,$identidade,$orgaoId,$dtId);
                $pessoal->gravar($campos,$valor,$idPessoa,"tbdocumentacao","idPessoa",FALSE);
                
                # Grava na tabela tbrecadastramento                
                $campos = array('idServidor','dataAtualizacao','idUsuario');
                $valor = array($idServidor,$data,$idUsuario);
                
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
