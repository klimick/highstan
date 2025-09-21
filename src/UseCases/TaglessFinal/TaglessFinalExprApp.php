<?php

declare(strict_types=1);

namespace Highstan\UseCases\TaglessFinal;

final readonly class TaglessFinalExprApp
{
    public static function main(): void
    {
        // You could think of ExprV1 as a tagged type — and technically, that’s correct.
        // In reality, ExprV1<A> is a Closure<R of type-lam<_>>(ExprSemV1<R>): R<A>
        // In other words, ExprV1<A> is just a polymorphic function.
        //
        // ExprV1 also provides a couple of “constructors” for building tagless values: add and num.
        // For example, here we build the expression (1 + 41):
        $fortyTwo = ExprV1::add(
            ExprV1::num(1),
            ExprV1::num(41),
        );

        // Since a tagless value is simply a function, interpreting it
        // means invoking that function with a concrete semantics:
        var_dump($fortyTwo(ExprEvaluatorV1::Semantics));
        var_dump($fortyTwo(ExprStringifierV1::Semantics));

        // If we want to extend our language of expressions, we can introduce ExprV2.
        // This version adds a new constructor: eq (equality).
        //
        // To integrate ExprV1 expressions with ExprV2, we use ExprV2::lift.
        // This method adapts an ExprV1 into an ExprV2.
        //
        // Conceptually:
        //   ExprV1 is Closure<R of type-lam<_>>(ExprSemV1<R>): R<A>
        //   ExprV2 is Closure<R of type-lam<_>>(ExprSemV1<R>, ExprSemV2<R>): R<A>
        // That is, ExprV2 adds an extra semantics parameter.
        $eq = ExprV2::eq(
            ExprV2::lift(ExprV1::add(
                ExprV1::num(1),
                ExprV1::num(41),
            )),
            ExprV2::lift(ExprV1::add(
                ExprV1::num(41),
                ExprV1::num(1),
            )),
        );

        // Now we can interpret our extended expression
        // using both the old semantics and the new one:
        var_dump($eq(ExprEvaluatorV1::Semantics, ExprEvaluatorV2::Semantics));
        var_dump($eq(ExprStringifierV1::Semantics, ExprStringifierV2::Semantics));

        // This is something you *cannot* achieve with the traditional approach
        // using subtype polymorphism or algebraic data types (ADTs).
        //
        // The tagless final style gives us true extensibility in two directions:
        // - Adding new kinds of expressions (data variants)
        // - Adding new interpreters (operations)
        //
        // At the same time we preserve:
        // - Type safety (no dynamic typing)
        // - Modular extensions
        // - No copy-paste
        //
        // And additionally:
        // - No reflection — interpreters cannot inspect values (e.g. var_dump, print_r won’t work)
        // - No built-in operators, including universal equality
        // - Converting from one tagless representation to another is possible, but non-trivial
    }
}
