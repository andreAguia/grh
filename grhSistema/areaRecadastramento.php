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
            # Botao voltar
            botaoVoltar("grh.php");
            
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
                      WHERE tbservidor.situacao = 1
                   ORDER BY tbpessoa.nome';

            $result = $pessoal->select($select);
            
            $tabela = new Tabela();
            $tabela->set_titulo('Área de Recadastramento');
            $tabela->set_label(array('IdFuncional','Nome','Cargo','Lotação','Atualizado em:','Usuario','Editar'));
            #$relatorio->set_width(array(10,30,30,0,10,10,10));
            $tabela->set_align(array("center","left","left","left"));
            $tabela->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));

            $tabela->set_classe(array(NULL,NULL,"pessoal","pessoal"));
            $tabela->set_metodo(array(NULL,NULL,"get_Cargo","get_Lotacao"));

            # Botão de exibição dos servidores com permissão a essa regra
            $botao = new BotaoGrafico();
            $botao->set_label('');
            $botao->set_title('Editar Usuário');
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
                             tbpessoa.cep  
                        FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                       WHERE tbservidor.idServidor = $id";

            $result = $pessoal->select($select,false);
            
            
            # Formuário exemplo de login
            $form = new Form('?fase=valida','Atualiza dados?');

            # Telefone Residencial
            $controle = new Input('telResidencial','texto','Telefone Residencial:',1);
            $controle->set_size(30);
            $controle->set_linha(2);
            $controle->set_autofocus(TRUE); 
            $controle->set_valor($result['telResidencial']);
            $controle->set_col(6);
            $form->add_item($controle);
            
            # Telefone Celular
            $controle = new Input('telCelular','texto','Telefone Celular:',1);
            $controle->set_size(30);
            $controle->set_linha(3);
            $controle->set_valor($result['telCelular']);
            $controle->set_col(6);
            $form->add_item($controle);
            
            # Outro telefone para recado
            $controle = new Input('telRecados','texto','Outro telefone para recado:',1);
            $controle->set_size(30);
            $controle->set_linha(4);
            $controle->set_valor($result['telRecados']);
            $controle->set_col(6);
            $form->add_item($controle);
            
            # Email institucional da Uenf
            $controle = new Input('emailUenf','texto','Email institucional da Uenf:',1);
            $controle->set_size(30);
            $controle->set_linha(5);
            $controle->set_valor($result['emailUenf']);
            $controle->set_col(6);
            $form->add_item($controle);
            
            # Email Pessoal
            $controle = new Input('emailPessoal','texto','Email Pessoal:',1);
            $controle->set_size(30);
            $controle->set_linha(6);
            $controle->set_valor($result['emailPessoal']);
            $controle->set_col(6);
            $form->add_item($controle);
            
            # Endereço
            $controle = new Input('endereco','texto','Endereço do Servidor:',1);
            $controle->set_size(150);
            $controle->set_linha(7);
            $controle->set_valor($result['endereco']);
            $controle->set_col(12);
            $form->add_item($controle);
            
            # Bairro
            $controle = new Input('bairro','texto','Bairro:',1);
            $controle->set_size(50);
            $controle->set_linha(8);
            $controle->set_valor($result['bairro']);
            $controle->set_col(4);
            $form->add_item($controle);
            
            # Bairro
            $controle = new Input('bairro','texto','Bairro:',1);
            $controle->set_size(50);
            $controle->set_linha(8);
            $controle->set_valor($result['bairro']);
            $controle->set_col(4);
            $form->add_item($controle);
            
            # submit
            $controle = new Input('submit','submit');
            $controle->set_valor('Atualizar');
            $controle->set_linha(3);
            $controle->set_tabIndex(3);
            $controle->set_accessKey('E');
            $form->add_item($controle);

            $form->show();
            break;
        
################################################################
        
    }
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}else{
    loadPage("../../areaServidor/sistema/login.php");
}
