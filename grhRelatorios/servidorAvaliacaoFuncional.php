
<?php
/**
 * Relatório
 *    
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado
$idServidorPesquisado = NULL;	# Servidor Editado na pesquisa do sistema do GRH

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso){
    
    # Conecta ao Banco de Dados    
    $pessoal = new Pessoal();
    
    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();
    
    # Limita a página
    $grid = new Grid();
    $grid->abreColuna(12);
    
    ######
    
    #$relatorio = new Relatorio();
    #$relatorio->set_totalRegistro(FALSE);
    #$relatorio->set_dataImpressao(FALSE);
    #$relatorio->show();
    
    # Desempenho Profissional
    $grid = new Grid();
    $grid->abreColuna(12);
        p('Avaliação Funcional Anual','center','f16');
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    # 1) IDENTIFICAÇÃO:
    p('1) IDENTIFICAÇÃO:','f14');
    
    # Informação do Avaliador
    $grid = new Grid();
    $grid->abreColuna(2);
        p('IdFuncional:','fichaAvaliacaoLabel');
    $grid->fechaColuna();
    $grid->abreColuna(3);
        p('Nome:','fichaAvaliacaoLabel');
    $grid->fechaColuna();
    $grid->abreColuna(4);
        p('Cargo:','fichaAvaliacaoLabel');
    $grid->fechaColuna();
    $grid->abreColuna(2);
        p('Lotação:','fichaAvaliacaoLabel');
    $grid->fechaColuna();
    $grid->abreColuna(1);
        p('Admissão:','fichaAvaliacaoLabel');
    $grid->fechaColuna();
    
    $grid->abreColuna(2);
        p($pessoal->get_idFuncional($idServidorPesquisado),'fichaAvaliacaoCampo');
    $grid->fechaColuna();
    $grid->abreColuna(3);
        p($pessoal->get_nome($idServidorPesquisado),'fichaAvaliacaoCampo');
    $grid->fechaColuna();
    $grid->abreColuna(4);
        p($pessoal->get_cargo($idServidorPesquisado),'fichaAvaliacaoCampo');
    $grid->fechaColuna();
    $grid->abreColuna(2);
         p($pessoal->get_lotacao($idServidorPesquisado),'fichaAvaliacaoCampo');
    $grid->fechaColuna();
    $grid->abreColuna(1);
         p($pessoal->get_dtAdmissao($idServidorPesquisado),'fichaAvaliacaoCampo');
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    ######
    
    br();

    # Informação do Avaliador
    $grid = new Grid();
    $grid->abreColuna(3);
        p('Chefia Imediata:','fichaAvaliacaoLabel');
    $grid->fechaColuna();
    $grid->abreColuna(3);
        p('IdFuncional:','fichaAvaliacaoLabel');
    $grid->fechaColuna();
    $grid->abreColuna(3);
        p('Lotação:','fichaAvaliacaoLabel');
    $grid->fechaColuna();
    $grid->abreColuna(3);
        p('Cargo:','fichaAvaliacaoLabel');
    $grid->fechaColuna();
    
    $grid->abreColuna(3);
        p('_____________________');
    $grid->fechaColuna();
    $grid->abreColuna(3);
        p('_____________________');
    $grid->fechaColuna();
    $grid->abreColuna(3);
        p('_____________________');
    $grid->fechaColuna();
    $grid->abreColuna(3);
        p('_____________________');
    $grid->fechaColuna();
    $grid->fechaGrid();

    br();
    
    # Período Avaliado
    $grid = new Grid();
    $grid->abreColuna(6);
        p('Período Avaliado:','fichaAvaliacaoLabel');
    $grid->fechaColuna();
    $grid->abreColuna(6);
        p('Data da avaliação:','fichaAvaliacaoLabel');
    $grid->fechaColuna();
    
    $grid->abreColuna(6);
        p('_________/_________  a  _________/_________','fichaAvaliacaoCampo');
    $grid->fechaColuna();
    $grid->abreColuna(6);
        p('__________/_________/_________','fichaAvaliacaoCampo');
    $grid->fechaColuna();
    $grid->fechaGrid();

    br();

    # Desempenho Profissional
    $grid = new Grid();
    $grid->abreColuna(12);
        p('DESEMPENHO PROFISSIONAL','center','f16');
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    br();

    # 2) FICHA DE AVALIA��O FUNCIONAL:
    p('2) FICHA DE AVALIAÇÂO FUNCIONAL:','f14');
    
    # Itens de Avaliação
    $item[0] = 'Escala de Pontos&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;==>';
    $item[1] = '1 - ASSIDUIDADE - Comparecimento ao trabalho para execução das tarefas respeitando a jornada de trabalho exigida. ';
    $item[2] = '2 - RESPONSABILIDADE - Comprometimento no exercício das atribuições inerentes ao cargo, levando-se em conta a seriedade, a dedicação e o interesse demonstrados no seu desempenho.';
    $item[3] = '3 - DISCIPLINA - Reconhecer as hierarquias de trabalho dentro dos princípios do respeito, e da legalidade.';
    $item[4] = '4 - INICIATIVA - Capacidade de agir com presteza, independência e adequação diante de situações que fujam a rotina de trabalho.';
    $item[5] = '5 - PRODUTIVIDADE - Refere-se a quantidade de trabalho e resultados obtidos, dentro dos prazos estabelecidos.';
    $item[6] = 'SubTotal';
    $item[7] = 'Total';
    
    # Monta a tabela
    
    echo '<table id="faf1">';
    foreach ($item as $key => $linha){
        echo '<tr>';
            if($key == 0){
                echo '<th>';
                p($linha,'f13');
                echo '</th>';
            }else{
                echo '<td>';
                p($linha,'f13');
                echo '</th>';
            }

            if($key == 0){
                for ($i = 1; $i < 6; $i++){
                    echo '<th align="center" width="6%">'.$i.'</td>';
                }
            }else{
                for ($i = 1; $i < 6; $i++){
                    echo '<td width="6%">&nbsp;</td>';
                }
            }
        echo '</tr>';
    }
    echo '</table>';
    
    br();
    
    # 3) RITÉRIOS DA AVALIAÇÂO FUNCIONAL:
    p('3) CRITÉRIOS DA AVALIAÇÂO FUNCIONAL:','f14');
    
    # Itens
    $criterio[0] = Array('&nbsp;','GRAU','PONTOS','SÍMBOLO');
    $criterio[1] = Array('(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)','Excelente','(21 a 25)','E');
    $criterio[2] = Array('(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)','Muito Bom','(16 a 20)','MB');
    $criterio[3] = Array('(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)','Bom','(11 a 15)','B');
    $criterio[4] = Array('(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)','Fraco','(06 a 10)','F');
    $criterio[5] = Array('(&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)','Insuficiente','(01 a 05)','I');


    # Monta a tabela
    echo '<table id="faf2">';
    foreach ($criterio as $linha){
        echo '<tr>';
            for ($i = 0; $i < 4; $i++){
                echo '<td align="center" width="20%">'.$linha[$i].'</td>';
            }
        echo '</tr>';
    }
    echo '</table>';

    br();
    
    # Assinaturas
    p('Avaliador: ____________________________________________________','pFafItem');
    p('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Avaliador (Assinatura e carimbo)','pFafItem');
    
    br(2);

    echo '<table width="100%" border="0">';
    echo '<tr><td>';
        p('Ciente do Servidor: _____________________________________________');
    echo '</td><td>';
        p('_________/________/________','pFafItem');
    echo '</td></tr>';
    echo '<tr><td>';
        p('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Assinatura e carimbo)','pFafItem');
    echo '</td><td>';
        p('&nbsp;&nbsp;&nbsp;&nbsp;Data da Ciência do Servidor','pFafItem');
    echo '</td></tr>';
    echo '</table>';

    # data de impressão
    p('Emitido em: '.date('d/m/Y - H:i:s'),'pRelatorioDataImpressao');
    
    $grid->fechaColuna();
    $grid->fechaGrid();
    
    $page->terminaPagina();
}