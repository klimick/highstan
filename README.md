# Highstan

Higher kinded type for PHPStan.

### 1. Regular Types
A **type** describes concrete data:
- `int` → a number
- `string` → text
- `bool` → true or false

---

### 2. Type Constructors
Some types are not concrete by themselves.  
They need another type to form a complete type:

- `list<Int>` → a list of numbers  
- `Option<String>` → either a string or nothing  

Here, `list` and `Option` are **type constructors**.  
They are "templates" that require another type to become concrete.

---

### 3. Higher-Kinded Types
Sometimes we want to write code that works with *any* type constructor,  
as long as it provides certain capabilities.

Example: **map**
- For `list<Int>`, `map` applies a function to every element in the list.  
- For `Option<Int>`, `map` applies a function only if a value exists.  

Instead of writing separate implementations, we can describe the ability  
to be "mappable" as a **Higher-Kinded Type**.

---

### 4. Why It Matters
Higher-Kinded Types let us:
- Write generic code that works across many type constructors.  
- Define abstractions like `Functor`, `Monad`, or `Traversable`.  
- Reduce duplication by expressing operations at a higher level.

---

### 5. Summary
- **Type** = concrete data (`int`, `string`).  
- **Type constructor** = needs another type to form data (`list<A>`, `Option<A>`).  
- **Higher-Kinded Type** = abstraction over type constructors,  
  allowing us to define common operations (like `map`) in a reusable way.  

## Examples

- [GADT Visitor](https://github.com/klimick/highstan/tree/master/src/UseCases/GADT)
- [Tagless final](https://github.com/klimick/highstan/tree/master/src/UseCases/TaglessFinal)
- [Functor, Applicative, Monad](https://github.com/klimick/highstan/tree/master/src/UseCases/Cats)

## TODO

- Detailed tests (besides examples)
- Wrap to PHPStan extension
