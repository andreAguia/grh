<?php

/**
 * Dados Gerais do servidor
 *  
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    $intra = new Intra();

    # Verifica a fase do programa
    if (Verifica::acesso($idUsuario, 12)) {
        $fase = get('fase', 'editar');
    } else {
        $fase = get('fase', 'ver');
    }

    # Verifica se veio menu grh e registra o acesso no log
    $grh = get('grh', false);
    if ($grh) {
        # Grava no log a atividade
        $atividade = "Cadastro do servidor - Dados funcionais";
        $data = date("Y-m-d H:i:s");
        $intra->registraLog($idUsuario, $data, $atividade, null, null, 7, $idServidorPesquisado);
    }

    # Verifica de onde veio
    $origem = get_session("origem");

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Cabeçalho da Página
    AreaServidor::cabecalho();

    # Abre um novo objeto Modelo
    $objeto = new Modelo();

    ################################################################
    # Exibe os dados do Servidor
    $objeto->set_rotinaExtra("get_DadosServidor");
    $objeto->set_rotinaExtraParametro($idServidorPesquisado);

    # Pega o perfil do Servidor    
    $perfilServidor = $pessoal->get_idPerfil($idServidorPesquisado);

    if (($perfilServidor == 1) OR ($perfilServidor == 4)) {
        # Verifica o regime do servidor
        $conc = new Concurso();
        $regime = $conc->get_regime($pessoal->get_idConcurso($idServidorPesquisado));
    } else {
        $regime = null;
    }

    # Nome do Modelo (aparecerá nos fildset e no caption da tabela)
    $objeto->set_nome('Dados Funcionais');

    # select do edita
    $selectEdita = 'SELECT idFuncional,
                           idPerfil,
                           situacao,
                           idCargo,                           
                           matricula,';

    if (($perfilServidor == 1) OR ($perfilServidor == 4)) {
        # Se houve transformação de regime
        if ($regime == "CLT") {
            $selectEdita .= 'dtTransfRegime,';
        }
    }

    if ($pessoal->temOpcaoFenorteUenf($idServidorPesquisado)) {
        $selectEdita .= 'opcaoFenorteUenf,';
    }

    $selectEdita .= 'nomenclaturaOriginal,';

    if (($perfilServidor == 1) OR ($perfilServidor == 4)) {
        $selectEdita .= 'dtEstagio,';
    }

    $selectEdita .= 'dtAdmissao,
                     processoAdm,
                     dtPublicAdm,
                     ciGepagAdm,
                     dtDemissao,
                     processoExo,
                     dtPublicExo,
                     ciGepagExo,
                     motivo,
                     tipoAposentadoria,
                     motivoDetalhe
                FROM tbservidor
               WHERE idServidor = ' . $idServidorPesquisado;

    # Habilita o modo leitura para usuario de regra 12
    if (Verifica::acesso($idUsuario, 12)) {
        $objeto->set_modoLeitura(true);
    }

    $objeto->set_selectEdita($selectEdita);

    # Caminhos
    $objeto->set_linkGravar('?fase=gravar');
    $objeto->set_linkListar('servidorMenu.php');
    $objeto->set_voltarForm('servidorMenu.php');

    # retira o botão incluir
    $objeto->set_botaoIncluir(false);

    # Classe do banco de dados
    $objeto->set_classBd('Pessoal');

    # Nome da tabela
    $objeto->set_tabela('tbservidor');

    # Nome do campo id
    $objeto->set_idCampo('idServidor');

    # Tipo de label do formulário
    $objeto->set_formlabelTipo(1);

    # Pega os dados da combo perfil
    $perfil = $pessoal->select('SELECT idperfil,
                                       nome,
                                       tipo
                                  FROM tbperfil
                              ORDER BY tipo, nome');

    array_unshift($perfil, [null, null]);

    # Pega os dados da combo cargo
    $cargo = $pessoal->select('SELECT idcargo,
                                      concat(tbtipocargo.cargo," - ",tbarea.area," - ",nome)
                                 FROM tbcargo LEFT JOIN tbtipocargo USING (idTipoCargo)
                                              LEFT JOIN tbarea USING (idarea)
                             ORDER BY tbtipocargo.cargo,tbarea.area,nome');

    array_unshift($cargo, [0, null]);

    # Pega os dados da combo situação
    $situacao = $pessoal->select('SELECT idsituacao,
                                         situacao
                                    FROM tbsituacao
                                ORDER BY situacao');

    array_unshift($situacao, [null, null]);

    # Pega os dados da combo motivo de Saída do servidor
    $motivo = $pessoal->select('SELECT idmotivo,
                                       motivo
                                  FROM tbmotivo
                              ORDER BY motivo');

    array_unshift($motivo, [null, null]);

    $colunaCargo = 12;

    # Campos para o formulario
    $campos = array(
        array('linha' => 1,
            'nome' => 'idFuncional',
            'label' => 'id Funcional:',
            'tipo' => 'texto',
            'autofocus' => true,
            'size' => 10,
            'col' => 2,
            'title' => 'Número da id funcional do servidor.'),
        array('linha' => 1,
            'nome' => 'idPerfil',
            'label' => 'Perfil:',
            'tipo' => 'combo',
            'required' => true,
            'optgroup' => true,
            'array' => $perfil,
            'title' => 'Perfil do servidor',
            'col' => 3,
            'size' => 15),
        array('linha' => 1,
            'nome' => 'situacao',
            'label' => 'Situação:',
            'tipo' => 'combo',
            'array' => $situacao,
            'col' => 2,
            'title' => 'Situação',
            'size' => 15));

    # Esconde quando for cedido, pois a matrícula que
    #  conta é a do orgão de origem
    if ($perfilServidor == 2) {
        array_push($campos,
                array('linha' => 1,
                    'nome' => 'matricula',
                    'label' => 'Matricula Uenf:',
                    'tipo' => 'hidden',
                    'size' => 10,
                    'unique' => true,
                    'col' => 2,
                    'title' => 'Matrícula do servidor.'));
    } else {
        array_push($campos,
                array('linha' => 1,
                    'nome' => 'matricula',
                    'label' => 'Matricula Uenf:',
                    'tipo' => 'texto',
                    'size' => 10,
                    'unique' => true,
                    'col' => 2,
                    'title' => 'Matrícula do servidor.'));
    }

    array_push($campos,
            array('linha' => 2,
                'nome' => 'idCargo',
                'label' => 'Cargo / Área / Função:',
                'tipo' => 'combo',
                'array' => $cargo,
                'title' => 'Cargo',
                'col' => 12,
                'size' => 15));

    if (($perfilServidor == 1) OR ($perfilServidor == 4)) {
        if ($regime == "CLT") {

            array_push($campos,
                    array('linha' => 3,
                        'nome' => 'dtTransfRegime',
                        'label' => 'Data da transformação de regime:',
                        'helptext' => 'Transformação para estatutário conforme Lei 4.152 de 08/09/2003.:',
                        'tipo' => 'data',
                        'size' => 20,
                        'col' => 3,
                        'title' => 'Data da Transformação do regime de Celetista para estatutário'));
            $colunaCargo -= 3;
        }
    }

    if ($pessoal->temOpcaoFenorteUenf($idServidorPesquisado)) {
        array_push($campos,
                array('linha' => 3,
                    'nome' => 'opcaoFenorteUenf',
                    'label' => 'Optou ser transferido?',
                    'helptext' => 'Quando da separação das Instituições FENORTE e UENF (Lei nº3.684 de 23/10/2001) o servidor optou por trasnferir-se da FENORTE para a UENF?',
                    'tipo' => 'simnao2',
                    'size' => 10,
                    'col' => 3,
                    'padrao' => 0,
                    'title' => 'Se o servidor optou em se transferir da FENORTE para e UENF de acordo com Lei nº 3684 de 23/10/2001'));
        $colunaCargo -= 3;
    }

    # os demais
    array_push($campos,
            array('linha' => 3,
                'nome' => 'nomenclaturaOriginal',
                'label' => 'Nomenclatura Original do Cargo:',
                'tipo' => 'texto',
                'helptext' => 'Informe a nomenclatura original do cargo, caso a mesma tenha sido alterada pelo Decreto 28950 de 15/08/2001, Lei 4798/2006 de 30/06/2006 e Lei 4800/2006 de 30/06/2006.',
                'col' => $colunaCargo,
                'size' => 200,
                'title' => 'Cargo de origem, que consta no ato de investidura.'));

    if (($perfilServidor == 1) OR ($perfilServidor == 4)) {
        array_push($campos,
                array('linha' => 4,
                    'nome' => 'dtEstagio',
                    'label' => 'Data de Designação do Estagio Experimental:',
                    'tipo' => 'data',
                    'size' => 20,
                    'col' => 3,
                    'fieldset' => 'Dados do Estagio Experimental',
                    'title' => 'Data de designação do estagio experimental.'));
    }

    array_push($campos,
            array('linha' => 5,
                'nome' => 'dtAdmissao',
                'label' => 'Data de Admissão:',
                'tipo' => 'data',
                'size' => 20,
                'col' => 3,
                'fieldset' => 'Dados da Admissão',
                'required' => true,
                'title' => 'Data de Admissão.'),
            array('linha' => 5,
                'nome' => 'processoAdm',
                'label' => 'Processo de Admissão:',
                'tipo' => 'texto',
                'col' => 3,
                'size' => 25,
                'title' => 'Número do processo de admissão.'),
            array('linha' => 5,
                'nome' => 'dtPublicAdm',
                'label' => 'Data da Publicação:',
                'tipo' => 'data',
                'size' => 20,
                'col' => 3,
                'title' => 'Data da Publicação no DOERJ.'),
            array('linha' => 5,
                'nome' => 'ciGepagAdm',
                'label' => 'Documento:',
                'tipo' => 'texto',
                'size' => 30,
                'col' => 3,
                'title' => 'Documento informando a admissão.'),
            array('linha' => 6,
                'nome' => 'dtDemissao',
                'label' => 'Data da Saída:',
                'tipo' => 'data',
                'col' => 3,
                'fieldset' => 'Dados da Saída do Servidor',
                'size' => 20,
                'title' => 'Data da Saída do Servidor.'),
            array('linha' => 6,
                'nome' => 'processoExo',
                'label' => 'Processo:',
                'tipo' => 'processo',
                'size' => 25,
                'col' => 3,
                'title' => 'Número do processo.'),
            array('linha' => 6,
                'nome' => 'dtPublicExo',
                'label' => 'Data da Publicação:',
                'tipo' => 'data',
                'size' => 20,
                'col' => 3,
                'title' => 'Data da Publicação no DOERJ.'),
            array('linha' => 6,
                'nome' => 'ciGepagExo',
                'label' => 'Documento:',
                'tipo' => 'texto',
                'size' => 30,
                'col' => 3,
                'title' => 'Documento informando a saída do servidor'),
            array('linha' => 7,
                'nome' => 'motivo',
                'label' => 'Motivo:',
                'tipo' => 'combo',
                'array' => $motivo,
                'col' => 4,
                'size' => 30,
                'title' => 'Motivo da Saida do Servidor.'),
            array('linha' => 7,
                'nome' => 'tipoAposentadoria',
                'label' => 'Tipo:',
                'tipo' => 'combo',
                'array' => array("", "Integral", "Proporcional"),
                'title' => 'Tipo de Aposentadoria',
                'col' => 2,
                'size' => 15),
            array('linha' => 7,
                'nome' => 'motivoDetalhe',
                'label' => 'Motivo Detalhado:',
                'tipo' => 'texto',
                'size' => 100,
                'col' => 6,
                'title' => 'Motivo detalhado da Saida do Servidor.')
    );

    $objeto->set_campos($campos);

    # Log
    $objeto->set_idUsuario($idUsuario);
    $objeto->set_idServidorPesquisado($idServidorPesquisado);
    ################################################################

    switch ($fase) {
        case "ver" :
        case "editar" :

            $dtadmissao = $pessoal->get_dtAdmissao($idServidorPesquisado);
            $dtTranfRegime = $pessoal->get_dtTranfRegime($idServidorPesquisado);
            $nomenclaturaOgiginal = $pessoal->get_nomenclaturaOriginal($idServidorPesquisado);
            $mensagem = null;

            # Informa se o servidor entrou como CLT
            if ($regime == "CLT") {
                $mensagem .= "- Servidor admitido sob o regime da CLT em {$dtadmissao}.<br/>";

                # Verifica se foi transformado
                if (!empty($dtTranfRegime)) {
                    $mensagem .= "- Transformado em regime estatutário em {$dtTranfRegime}, conforme Lei 4.152 de 08/09/2003, publicada no DOERJ de 09/09/2003.";
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

            # Exibe a mensagem
            if (!empty($mensagem)) {
                $objeto->set_rotinaExtraEditar(array("callout"));
                $objeto->set_rotinaExtraEditarParametro(array($mensagem));
            }


            $objeto->$fase($idServidorPesquisado);
            break;

        case "gravar" :
            $objeto->gravar($idServidorPesquisado, 'servidorFuncionaisExtra.php');
            break;
    }
    $page->terminaPagina();
} else {
    loadPage("../../areaServidor/sistema/login.php");
}