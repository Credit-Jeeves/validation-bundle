function extend(Child, Parent) {
    Child.__proto__ = Parent;
    Child.superclass = Parent;
}
