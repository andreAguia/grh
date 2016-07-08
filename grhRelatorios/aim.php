<?php
/**
 * Sistema GRH
 * 
 * Relatório de AIM
 *   
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $intra = new Intra();
    $pessoal = new Pessoal();
	
    # Verifica a fase do programa
    $fase = get('fase','');

    # pega o id da licença
    $id = get_session('sessionAim');

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    # Cabeçalho da Página
    if($fase == "")
        AreaServidor::cabecalho();

    # Dados do relatório que "teoricamente" não mudam. (mas podem mudar)
    $secretaria = "SECRETARIA DE ESTADO DE CIÊNCIA E TECNOLOGIA";
    $superintendencia = "SUPERINTENDÊNCIA CENTRAL DE PERÍCIA MÉDICA E SAÚDE OCUPACIONAL";
    $fundacao = "Fundação Estadual Norte Fluminense";
    $endereco_fundacao = "Av. Alberto Lamego 2000 - Horto - Campos dos Goytacazes - RJ";
    $telefone_fundacao = "2739-5540";
    $declaracao = "Informamos que o servidor não responde a inquérito administrativo por faltas nessa instituição.";

    switch ($fase)
    {
        case "" :
            # Limita o tamanho da tela
            $grid = new Grid();
            $grid->abreColuna(12);
                
            # pega os parâmetros da AIM
            $fieldset = new Fieldset('Emissão de AIM - Apresentação para Inspeção Médica','login');
            #$fieldset->set_position('absolute','10%','30%');
            #$fieldset->set_width('80%');
            #$fieldset->set_backgroundColor('#eee');
            $fieldset->abre();

            $form = new Form('?fase=valida');
            #$form->set_withTable(true);
            #$form->set_foco('dataAim');

                # Data do Aim
                $controle = new Input('dataAim','data','Data do Aim:',1);
                $controle->set_size(10);
                $controle->set_linha(1);
                $controle->set_col(4);
                $controle->set_title('A data do AIM');
                $controle->set_required(true);
                $form->add_item($controle);

                # Exame
                $controle = new Input('exame','texto','Exame na Pessoa de:',1);
                $controle->set_size(50);
                $controle->set_linha(1);
                $controle->set_col(5);
                $controle->set_title('Nome do Paciente');
                $controle->set_required(true);
                $form->add_item($controle);

                # Pega os dados da combo parentesco
                $lista = new Pessoal();
                $result = $lista->select('SELECT idParentesco, 
                                                 Parentesco
                                            FROM tbparentesco
                                        ORDER BY parentesco');
                array_push($result, array(0,null)); # Adiciona o valor de nulo

                # Parentesco
                $controle = new Input('parentesco','combo','Parentesco:',1);
                $controle->set_size(20);
                $controle->set_linha(1);
                $controle->set_col(3);
                $controle->set_title('Parentesco');
                $controle->set_array($result);
                $form->add_item($controle);

                # faltando
                $controle = new Input('faltando','combo','Faltando:',1);
                $controle->set_size(10);
                $controle->set_linha(2);
                $controle->set_col(4);
                $controle->set_title('Faltando o Serviço?');
                $controle->set_array(array("Não","Sim"));
                $controle->set_required(true);
                $form->add_item($controle);

                # Desde
                $controle = new Input('desde','data','Desde:',1);
                $controle->set_size(10);
                $controle->set_linha(2);
                $controle->set_col(4);
                $controle->set_title('Desde quando está faltando');
                $form->add_item($controle);            

                # Perícia
                $controle = new Input('pericia','data','Perícia agendada para:',1);
                $controle->set_size(10);
                $controle->set_linha(2);
                $controle->set_col(4);
                $controle->set_title('Data do agendamento da perícia');
                $form->add_item($controle);

                # submit
                $controle = new Input('submit','submit');
                $controle->set_valor(' Prosseguir ');
                $controle->set_size(20);
                $controle->set_linha(3);
                #$controle->set_formAlign('center');
                #$controle->set_tabIndex(3);
                $controle->set_accessKey('E');
                $form->add_item($controle);

            $form->show();		

            $fieldset->fecha();
            $grid->fechaColuna();
            $grid->fechaGrid();
            break;

        case "valida" :
            # Pega os posts
            $data = post("dataAim");
            $exame = post("exame");
            $parentesco = post("parentesco");
            $faltando = post("faltando");
            $desde = post("desde");
            $pericia = post("pericia");
            
            # formata data quando vier de um controle html5 (vem yyyy/mm/dd)
            if(HTML5)
            {
                $data = date_to_php($data);
                $desde = date_to_php($desde);
                $pericia = date_to_php($pericia);
            }

            # inicia as variáveis de validação
            $msgErro = null;
            $erro = 0;

            # valida se dataAim está em branco
            if (is_null($data) or ($data == ''))
            {
                $erro = 1;
                $msgErro .= 'O campo data do Aim não pode der em branco\n';
            }

            # valida se exame está em branco
            if (is_null($exame) or ($exame == ''))
            {
                $erro = 1;
                $msgErro .= 'O campo nome da pessoa do exame não pode der em branco\n';
            }

            # valida se faltando está em branco
            if (is_null($faltando) or ($faltando == ''))
            {
                $erro = 1;
                $msgErro .= 'O campo faltando não pode der em branco\n';
            }

            # valida se desde é uma data válida
            if((!is_null($desde)) AND ($desde <>''))
            {
                if(!validaData($desde))
                {                    
                    $erro = 1;
                    $msgErro .= 'O campo desde não é data válida\n';
                }
            }

            # valida se dataAim é uma data válida
            if (!validaData($data))
            {
                $erro = 1;
                $msgErro .= 'O campo data do Aim não é data válida.';
            }

            # passa o campo parentesco para null se for 0
            if($parentesco == 0)
                $parentesco = null;

            if ($erro == 0)
            {
                ## Monta o Relatório    

                # Menu do alto do relatório (que supostamente não deveria ser impresso)
                $menuRelatorio = new menuRelatorio();
                $menuRelatorio->set_botaoVoltar(null);
                $menuRelatorio->show();

                # Cabeçalho do Relatório (com o logotipo)
                $cabecalho = new Relatorio();
                $cabecalho->exibeCabecalho();

                # Pega os dados do servidor
                $select = 'SELECT tbservidor.idFuncional,
                                  tbpessoa.nome,
                                  tbcargo.nome,
                                  tbpessoa.endereco,
                                  tbpessoa.complemento,
                                  tbpessoa.bairro,
                                  tbpessoa.cidade,
                                  tbpessoa.UF,
                                  tbpessoa.cep,
                                  tbpessoa.idPessoa
                             FROM tbservidor 
                        LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                        LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                            WHERE idServidor = '.$idServidorPesquisado;

                # conecta
                $pessoal = new Pessoal();
                $row = $pessoal->select($select,false);
                
                # Tamanho das colunas
                $col0 = 1; // margem esquerda
                $col1 = 4;
                $col2 = 7;
                
                # Título
                p("AIM - APRESENTAÇÃO PARA INSPEÇÃO MÉDICA","center");
                br();
                
                # Limita o tamanho da tela
                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("Data:","aim");
                $grid->fechaColuna();
                $grid->abreColuna($col2);
                    p($data,"aim");
                $grid->fechaColuna();
                $grid->fechaGrid();  

                # matrícula
                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("idFuncional:","aim");
                $grid->fechaColuna();
                $grid->abreColuna($col2);
                    p($row[0],"aim");
                $grid->fechaColuna();
                $grid->fechaGrid();  

                # nome
                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("Nome:","aim");
                $grid->fechaColuna();
                $grid->abreColuna($col2);
                    p($row[1],"aim");
                $grid->fechaColuna();
                $grid->fechaGrid(); 

                # cargo
                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("Cargo:","aim");
                $grid->fechaColuna();
                $grid->abreColuna($col2);
                    p($row[2],"aim");
                $grid->fechaColuna();
                $grid->fechaGrid(); 
                
                # secretaria
                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("Secretaria:","aim");
                $grid->fechaColuna();
                $grid->abreColuna($col2);
                    p($secretaria,"aim");
                $grid->fechaColuna();
                $grid->fechaGrid();

                # local do trabalho
                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("Local de Trabalho:","aim");
                $grid->fechaColuna();
                $grid->abreColuna($col2);
                    p($fundacao,"aim");
                $grid->fechaColuna();
                $grid->fechaGrid();

                # endereco
                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("Endereço:","aim");
                $grid->fechaColuna();
                $grid->abreColuna($col2);
                    p($endereco_fundacao,"aim");
                $grid->fechaColuna();
                $grid->fechaGrid();
                
                # telefone
                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("Telefone:","aim");
                $grid->fechaColuna();
                $grid->abreColuna($col2);
                    p($telefone_fundacao,"aim");
                $grid->fechaColuna();
                $grid->fechaGrid();      

                # exame
                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("Exame na pessoa de:","aim");
                $grid->fechaColuna();
                $grid->abreColuna($col2);
                    p($exame,"aim");
                $grid->fechaColuna();
                $grid->fechaGrid();

                # parentesco
                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("Parentesco:","aim");
                $grid->fechaColuna();
                $grid->abreColuna($col2);
                    if(Is_null($parentesco))
                    p('- - -',"aim");
                else
                    p($pessoal->get_parentesco($parentesco),"aim");
                $grid->fechaColuna();
                $grid->fechaGrid();

                # residencia
                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("Residência:","aim"); 
                $grid->fechaColuna();
                $grid->abreColuna($col2);
                    p($row[3]." - ".$row[4]." - ".$row[5]." - ".$row[6]." - ".$row[7],"aim");
                $grid->fechaColuna();
                $grid->fechaGrid();

                # telefones
                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("Telefones:","aim");
                $grid->fechaColuna();
                $grid->abreColuna($col2);
                    p($pessoal->get_telefones($idServidorPesquisado),"aim");
                $grid->fechaColuna();
                $grid->fechaGrid();

                # faltando
                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("Faltando ao Serviço:","aim");
                $grid->fechaColuna();
                $grid->abreColuna(2);
                    p($faltando,"aim");
                $grid->fechaColuna();
                if(!Is_null($desde)){
                    $grid->abreColuna($col2-2);
                    p("Desde: ".$desde,"aim"); 
                    $grid->fechaColuna();
                }
                $grid->fechaGrid();

                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("Perícia Agendada para:","aim");
                $grid->fechaColuna();
                $grid->abreColuna($col2);
                    p($pericia,"aim");
                $grid->fechaColuna();
                $grid->fechaGrid();

                # Observações
                $grid = new Grid();
                $grid->abreColuna($col0);
                $grid->fechaColuna();
                $grid->abreColuna($col1);
                    p("Observações de interesse para perícia médica:","aim");
                $grid->fechaColuna();
                $grid->abreColuna($col2);
                    p($declaracao,"aim");
                $grid->fechaColuna();
                $grid->fechaGrid();

                br();
                
                # Assinatura da Chefia Imediata
                p('__________________________________','center',"aim");
                p('Chefia Imediata','center',"aim");
                
                # Log
                $atividade = "Emitiu Aim de ".$pessoal->get_nome($idServidorPesquisado)." (idServidor: $idServidorPesquisado)";
                $data = date("Y-m-d H:i:s");
                $intra->registraLog($idUsuario,$data,$atividade,null,null,4);
            }else{
                alert($msgErro);
                back(1);
            }		
    }
    $page->terminaPagina();
}