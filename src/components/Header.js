import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { Link } from 'react-router-dom';
import '../styles/header.css';
export default function Header() {
    return (_jsx("header", { className: "header", children: _jsxs("div", { className: "header-container", children: [_jsx(Link, { to: "/", className: "logo", children: _jsx("h1", { children: "Lost & Found" }) }), _jsxs("nav", { className: "nav", children: [_jsx(Link, { to: "/", className: "nav-link", children: "Browse" }), _jsx(Link, { to: "/report", className: "nav-link btn-primary", children: "Report Item" })] })] }) }));
}
