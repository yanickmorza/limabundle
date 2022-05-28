<?= "<?php\n" ?>

namespace <?= $namespace ?>;

<?= $use_statements; ?>

<?php if ($use_attributes) { ?>
#[Route('<?= $route_path ?>')]
<?php } else { ?>
/**
 * @Route("<?= $route_path ?>")
 */
<?php } ?>
class <?= $class_name ?> extends AbstractController
{
<?= $generator->generateRouteForControllerMethod('/', sprintf('%s_index', $route_name), ['GET', 'POST']) ?>
<?php if (isset($repository_full_class_name) && $generator->repositoryHasAddRemoveMethods($repository_full_class_name)) { ?>
    public function index(Request $request, <?= $repository_class_name ?> $<?= $repository_var ?>): Response
<?php } else { ?>
    public function index(Request $request, EntityManagerInterface $entityManager, <?= $repository_class_name ?> $<?= $repository_var ?>): Response
<?php } ?>
    {
        $<?= $entity_var_singular ?> = new <?= $entity_class_name ?>();
        $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>);
        $form->handleRequest($request);

<?php if (isset($repository_full_class_name) && $generator->repositoryHasAddRemoveMethods($repository_full_class_name)) { ?>
        if ($form->isSubmitted() && $form->isValid()) {
            $<?= $repository_var ?>->add($<?= $entity_var_singular ?>, true);

            return $this->redirectToRoute('<?= $route_name ?>_index');
        }
<?php } else { ?>
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($<?= $entity_var_singular ?>);
            $entityManager->flush();

            return $this->redirectToRoute('<?= $route_name ?>_index');
        }
<?php } ?>

<?php if ($use_render_form) { ?>
        return $this->renderForm('<?= $templates_path ?>/index.html.twig', [
            '<?= $entity_twig_var_plural ?>' => $<?= $repository_var ?>->findAll(),
            'form' => $form,
            'edit' => $<?= $entity_var_singular ?>->getId() !== null,
            'is_show' => false,
        ]);
<?php } else { ?>
        return $this->render('<?= $templates_path ?>/index.html.twig', [
            '<?= $entity_twig_var_plural ?>' => $<?= $repository_var ?>->findAll(),
            'form' => $form->createView(),
            'edit' => $<?= $entity_var_singular ?>->getId() !== null,
            'is_show' => false,
        ]);
<?php } ?>
    }

<?= $generator->generateRouteForControllerMethod(sprintf('/{%s}/edit', $entity_identifier), sprintf('%s_edit', $route_name), ['GET', 'POST']) ?>
<?php if (isset($repository_full_class_name) && $generator->repositoryHasAddRemoveMethods($repository_full_class_name)) { ?>
    public function edit(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>, <?= $repository_class_name ?> $<?= $repository_var ?>): Response
<?php } else { ?>
    public function edit(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>, EntityManagerInterface $entityManager, <?= $repository_class_name ?> $<?= $repository_var ?>): Response
<?php } ?>
    {
        $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>);
        $form->handleRequest($request);

<?php if (isset($repository_full_class_name) && $generator->repositoryHasAddRemoveMethods($repository_full_class_name)) { ?>
        if ($form->isSubmitted() && $form->isValid()) {
            $<?= $repository_var ?>->add($<?= $entity_var_singular ?>, true);

            return $this->redirectToRoute('<?= $route_name ?>_index');
        }
<?php } else { ?>
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('<?= $route_name ?>_index');
        }
<?php } ?>

<?php if ($use_render_form) { ?>
        return $this->renderForm('<?= $templates_path ?>/index.html.twig', [
            '<?= $entity_twig_var_plural ?>' => $<?= $repository_var ?>->findAll(),
            'form' => $form,
            'edit' => $<?= $entity_var_singular ?>->getId() !== null,
            'is_show' => false,
        ]);
<?php } else { ?>
        return $this->render('<?= $templates_path ?>/index.html.twig', [
            '<?= $entity_twig_var_plural ?>' => $<?= $repository_var ?>->findAll(),
            'form' => $form->createView(),
            'edit' => $<?= $entity_var_singular ?>->getId() !== null,
            'is_show' => false,
        ]);
<?php } ?>
    }

<?= $generator->generateRouteForControllerMethod(sprintf('/{%s}/show', $entity_identifier), sprintf('%s_show', $route_name), ['GET']) ?>
    public function show(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>, <?= $repository_class_name ?> $<?= $repository_var ?>): Response
    {
        $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>, ['disabled' => true]);
        $form->handleRequest($request);

        return $this->renderForm('<?= $templates_path ?>/index.html.twig', [
            '<?= $entity_twig_var_plural ?>' => $<?= $repository_var ?>->findAll(),
            'form' => $form,
            'edit' => $<?= $entity_var_singular ?>->getId() !== null,
            'is_show' => true,
        ]);
    }

<?= $generator->generateRouteForControllerMethod(sprintf('/{%s}', $entity_identifier), sprintf('%s_delete', $route_name), ['POST']) ?>
<?php if (isset($repository_full_class_name) && $generator->repositoryHasAddRemoveMethods($repository_full_class_name)) { ?>
    public function delete(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>, <?= $repository_class_name ?> $<?= $repository_var ?>): Response
<?php } else { ?>
    public function delete(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>, EntityManagerInterface $entityManager): Response
<?php } ?>
    {
<?php if (isset($repository_full_class_name) && $generator->repositoryHasAddRemoveMethods($repository_full_class_name)) { ?>
        if ($this->isCsrfTokenValid('delete'.$<?= $entity_var_singular ?>->get<?= ucfirst($entity_identifier) ?>(), $request->request->get('_token'))) {
            $<?= $repository_var ?>->remove($<?= $entity_var_singular ?>, true);
        }
<?php } else { ?>
        if ($this->isCsrfTokenValid('delete'.$<?= $entity_var_singular ?>->get<?= ucfirst($entity_identifier) ?>(), $request->request->get('_token'))) {
            $entityManager->remove($<?= $entity_var_singular ?>);
            $entityManager->flush();
        }
<?php } ?>

        return $this->redirectToRoute('<?= $route_name ?>_index');
    }
}

